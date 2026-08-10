<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuditService;
use App\Services\GatewayFactory;

/**
 * Inbound carrier billing callbacks.
 *
 * Verify → record (idempotent on event_id) → respond 200 IMMEDIATELY → queue.
 * The state change is applied by queue_worker.php, never inline: a slow write
 * here would make the provider retry and the event would be applied twice.
 */
final class WebhookController extends Controller
{
    public function carrier(Request $request): Response
    {
        $raw = $request->rawBody();

        if (!GatewayFactory::make()->verifyWebhook($raw, $request->headers)) {
            Logger::warning('webhook.signature_invalid', ['ip' => $request->ip()]);
            AuditService::log('webhook.rejected', 'gateway', null, 'webhook', null, [], $request->ipHash());

            return Response::json(['status' => 'unauthorized'], 401);
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            return Response::json(['status' => 'bad_request'], 400);
        }

        $eventId = (string) ($payload['eventId'] ?? $payload['event_id'] ?? '');
        if ($eventId === '') {
            // No provider id to key on — derive a stable one from the body so a
            // retry of the same payload still collapses to one row.
            $eventId = 'sha1:' . sha1($raw);
        }

        $type = (string) ($payload['eventType'] ?? $payload['type'] ?? 'unknown');

        // The UNIQUE index on event_id is the idempotency guarantee.
        $inserted = Db::exec(
            'INSERT INTO webhook_events (event_id, type, payload, signature_ok)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE id = id',
            [substr($eventId, 0, 160), substr($type, 0, 60), (string) json_encode($payload, JSON_UNESCAPED_UNICODE)]
        );

        if ($inserted === 0) {
            Logger::info('webhook.duplicate', ['event_id' => substr($eventId, 0, 24)]);
            return Response::json(['status' => 'ok', 'duplicate' => true], 200);
        }

        Db::exec(
            'INSERT INTO jobs (queue, job, payload, available_at)
             VALUES ("default", "webhook.apply", ?, NOW())',
            [(string) json_encode(['event_id' => $eventId], JSON_UNESCAPED_UNICODE)]
        );

        return Response::json(['status' => 'ok'], 200);
    }
}
