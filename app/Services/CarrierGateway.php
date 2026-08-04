<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Logger;
use App\Exceptions\GatewayException;
use App\Exceptions\OtpException;
use App\Support\Operators;

/**
 * Production gateway. Every response is normalised inside this class, so no
 * other file in the project knows the carrier billing provider exists (spec §7.3).
 *
 * When the developer-portal docs arrive, only the constants below and the
 * three payload/response mapping methods at the bottom change.
 */
final class CarrierGateway implements SubscriptionGateway
{
    // ── TODO: fill from the carrier billing provider developer portal (the carrier billing provider's developer portal → your app → API) ──
    private const EP_OTP_SEND   = '/{{TODO_otp_send_path}}';
    private const EP_OTP_VERIFY = '/{{TODO_otp_verify_path}}';
    private const EP_CHARGE     = '/{{TODO_charge_path}}';
    private const EP_STATUS     = '/{{TODO_status_path}}';
    private const EP_UNSUB      = '/{{TODO_unsub_path}}';
    private const AUTH_SCHEME   = 'basic';  // TODO: basic | bearer | hmac
    private const SIG_HEADER    = 'x-signature';  // TODO: confirm header name
    // ─────────────────────────────────────────────────────────────────────

    public function sendOtp(string $msisdn): array
    {
        $this->assertConfigured(self::EP_OTP_SEND);

        $res = $this->post(self::EP_OTP_SEND, [
            'applicationId' => (string) config('carrier.app_id'),
            'password'      => (string) config('carrier.password'),
            'subscriberId'  => $this->msisdnForApi($msisdn),
        ]);

        $ref = (string) ($res['referenceNo'] ?? $res['reference'] ?? '');
        if ($ref === '') {
            throw new GatewayException('Gateway did not return an OTP reference.');
        }

        // No `code` key: verification is remote, not local.
        return ['ref' => $ref, 'expires_at' => time() + (int) config('app.otp.ttl', 300)];
    }

    public function verifyOtp(string $ref, string $code, string $msisdn): array
    {
        $this->assertConfigured(self::EP_OTP_VERIFY);

        $res = $this->post(self::EP_OTP_VERIFY, [
            'applicationId' => (string) config('carrier.app_id'),
            'password'      => (string) config('carrier.password'),
            'referenceNo'   => $ref,
            'otp'           => $code,
        ]);

        $status = strtoupper((string) ($res['statusCode'] ?? $res['status'] ?? ''));
        if ($status !== 'S1000' && $status !== 'SUCCESS') {
            throw new OtpException('invalid', 'Code মিলছে না।');
        }

        $subscriberRef = (string) ($res['subscriberId'] ?? '');
        if ($subscriberRef === '') {
            throw new GatewayException('Gateway did not return a subscriber reference.');
        }

        return ['subscriber_ref' => $subscriberRef];
    }

    public function charge(string $subscriberRef, string $idempotencyKey, float $amount): array
    {
        $this->assertConfigured(self::EP_CHARGE);

        try {
            $res = $this->post(self::EP_CHARGE, [
                'applicationId'   => (string) config('carrier.app_id'),
                'password'        => (string) config('carrier.password'),
                'subscriberId'    => $subscriberRef,
                'externalTrxId'   => $idempotencyKey,
                'amount'          => number_format($amount, 2, '.', ''),
                'currency'        => (string) config('carrier.currency', 'BDT'),
                'paymentInstrumentName' => 'Mobile Account',
            ]);
        } catch (GatewayException $e) {
            // A transport failure is NOT a decline — leave it pending so the
            // next cycle retries against the same idempotency key.
            Logger::warning('carrier.charge.transport_error', ['message' => $e->getMessage()]);
            return ['status' => 'pending', 'txn_id' => null, 'code' => 'TRANSPORT', 'raw' => []];
        }

        $code   = strtoupper((string) ($res['statusCode'] ?? ''));
        $status = match (true) {
            in_array($code, ['S1000', 'SUCCESS'], true) => 'success',
            in_array($code, ['P1000', 'PENDING'], true) => 'pending',
            default                                     => 'failed',
        };

        return [
            'status' => $status,
            'txn_id' => isset($res['internalTrxId']) ? (string) $res['internalTrxId'] : null,
            'code'   => $code !== '' ? $code : null,
            'raw'    => $res,
        ];
    }

    public function status(string $subscriberRef): array
    {
        $this->assertConfigured(self::EP_STATUS);

        $res = $this->post(self::EP_STATUS, [
            'applicationId' => (string) config('carrier.app_id'),
            'password'      => (string) config('carrier.password'),
            'subscriberId'  => $subscriberRef,
        ]);

        return ['status' => strtoupper((string) ($res['subscriptionStatus'] ?? 'UNKNOWN')), 'raw' => $res];
    }

    public function unsubscribe(string $subscriberRef): bool
    {
        $this->assertConfigured(self::EP_UNSUB);

        $res = $this->post(self::EP_UNSUB, [
            'applicationId' => (string) config('carrier.app_id'),
            'password'      => (string) config('carrier.password'),
            'subscriberId'  => $subscriberRef,
            'action'        => '0',
        ]);

        return in_array(strtoupper((string) ($res['statusCode'] ?? '')), ['S1000', 'SUCCESS'], true);
    }

    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $allowed = (array) config('carrier.webhook.ips', []);
        if ($allowed !== []) {
            $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
            if (!in_array($remote, $allowed, true)) {
                Logger::warning('carrier.webhook.ip_rejected');
                return false;
            }
        }

        $secret = (string) config('carrier.webhook.secret', '');
        if ($secret === '') {
            // No secret configured: the IP allowlist above is the only control.
            return $allowed !== [];
        }

        $sent = (string) ($headers[self::SIG_HEADER] ?? '');
        return $sent !== '' && hash_equals(hash_hmac('sha256', $rawBody, $secret), $sent);
    }

    // ── transport ───────────────────────────────────────────────────────

    private function assertConfigured(string $endpoint): void
    {
        if (str_contains($endpoint, 'TODO') || config('carrier.base_url') === '') {
            throw new GatewayException(
                'CarrierGateway is not configured yet. Fill the endpoint constants in '
                . __FILE__ . ' and set CARRIER_BASE_URL.'
            );
        }
    }

    private function post(string $endpoint, array $payload): array
    {
        $url = (string) config('carrier.base_url') . $endpoint;

        $headers = ['Content-Type: application/json', 'Accept: application/json'];

        switch (self::AUTH_SCHEME) {
            case 'bearer':
                $headers[] = 'Authorization: Bearer ' . (string) config('carrier.app_secret');
                break;
            case 'hmac':
                $headers[] = 'X-App-Id: ' . (string) config('carrier.app_id');
                $headers[] = 'X-Signature: ' . hash_hmac(
                    'sha256',
                    (string) json_encode($payload),
                    (string) config('carrier.app_secret')
                );
                break;
            case 'basic':
            default:
                $headers[] = 'Authorization: Basic ' . base64_encode(
                    (string) config('carrier.app_key') . ':' . (string) config('carrier.app_secret')
                );
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => (string) json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => (int) config('carrier.timeout', 15),
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $body   = curl_exec($ch);
        $errNo  = curl_errno($ch);
        $errMsg = curl_error($ch);
        $http   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errNo !== 0 || $body === false) {
            throw new GatewayException('cURL error ' . $errNo . ': ' . $errMsg);
        }

        $decoded = json_decode((string) $body, true);

        if ($http >= 400) {
            Logger::warning('carrier.http_error', ['endpoint' => $endpoint, 'http' => $http]);
            throw new GatewayException('Gateway returned HTTP ' . $http);
        }

        return is_array($decoded) ? $decoded : [];
    }

    /** the carrier billing provider generally expects the international form without a '+'. */
    private function msisdnForApi(string $msisdn): string
    {
        return 'tel:88' . (string) Operators::normalize($msisdn);
    }
}
