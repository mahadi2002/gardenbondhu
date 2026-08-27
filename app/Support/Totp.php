<?php
declare(strict_types=1);

namespace App\Support;

/**
 * RFC 6238 TOTP (30s step, 6 digits, HMAC-SHA1) built on nothing but
 * hash_hmac() — no Composer package, matching the rest of this codebase.
 *
 * SHA-1 is what every mainstream authenticator app (Google Authenticator,
 * Authy, 1Password, Microsoft Authenticator, Aegis, …) actually implements
 * for TOTP. RFC 6238 allows SHA-256/512 too, but app support for those is
 * inconsistent — SHA-1 here is a compatibility choice, not a weak one. The
 * secret (160 bits of random_bytes) and the 30s step are what carry the
 * actual security, the same way they do in every TOTP deployment in the wild.
 */
final class Totp
{
    private const DIGITS = 6;
    private const PERIOD = 30;
    private const ALGO   = 'sha1';
    private const ALPHA  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // RFC 4648 base32

    /** A fresh random secret (160 bits, RFC 4226's recommended HOTP key length), base32-encoded. */
    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    /**
     * otpauth://totp/... URI for scanning into an authenticator app.
     * No QR image is generated server-side — rendering one would either mean
     * a QR-encoding library (a new dependency) or shipping the secret off to
     * a third-party QR API (a real leak of the 2FA seed). Manual entry of
     * the secret, or opening this URI directly on the same device, covers
     * every authenticator app without either tradeoff.
     */
    public static function provisioningUri(string $secret, string $accountLabel, string $issuer): string
    {
        $label = rawurlencode($issuer) . ':' . rawurlencode($accountLabel);
        $query = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => self::DIGITS,
            'period'    => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return 'otpauth://totp/' . $label . '?' . $query;
    }

    /** The 6-digit code for a given moment (default: right now). */
    public static function code(string $secret, ?int $timestamp = null): string
    {
        return self::codeAtCounter($secret, self::counterFor($timestamp));
    }

    /**
     * Verify a user-entered code, tolerating one 30s step of clock drift
     * either side — the narrow allowance RFC 6238 itself recommends, not an
     * open-ended window.
     */
    public static function verify(string $secret, string $code, ?int $timestamp = null): bool
    {
        $code = trim($code);
        if (!preg_match('/^[0-9]{6}$/', $code)) {
            return false;
        }

        $counter = self::counterFor($timestamp);
        foreach ([0, -1, 1] as $drift) {
            if (hash_equals(self::codeAtCounter($secret, $counter + $drift), $code)) {
                return true;
            }
        }
        return false;
    }

    /** Groups of 4 for the "can't scan? type this in" fallback: "ABCD EFGH ...". */
    public static function formatForDisplay(string $secret): string
    {
        return trim(chunk_split($secret, 4, ' '));
    }

    private static function counterFor(?int $timestamp): int
    {
        return intdiv($timestamp ?? time(), self::PERIOD);
    }

    /** RFC 4226 §5.3 dynamic truncation, applied to an HOTP counter. */
    private static function codeAtCounter(string $secret, int $counter): string
    {
        $key  = self::base32Decode($secret);
        $data = pack('N2', 0, $counter); // 8-byte big-endian counter (high word always 0 here)

        $hash   = hash_hmac(self::ALGO, $data, $key, true);
        $offset = ord($hash[19]) & 0x0F;

        $binary = ((ord($hash[$offset]) & 0x7F) << 24)
                | ((ord($hash[$offset + 1]) & 0xFF) << 16)
                | ((ord($hash[$offset + 2]) & 0xFF) << 8)
                | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($binary % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private static function base32Encode(string $bytes): string
    {
        $bits = '';
        foreach (str_split($bytes) as $byte) {
            $bits .= str_pad(decbin(ord($byte)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $out .= self::ALPHA[bindec($chunk)];
        }

        return $out;
    }

    private static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $encoded) ?? '');

        $bits = '';
        foreach (str_split($encoded) as $char) {
            $pos = strpos(self::ALPHA, $char);
            if ($pos === false) {
                continue;
            }
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }

        $bytes = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr((int) bindec($chunk));
            }
            // A trailing chunk shorter than 8 bits is base32 padding, not data.
        }

        return $bytes;
    }
}
