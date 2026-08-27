<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Crypto;
use App\Core\Db;

/**
 * Password reset tokens. Only a keyed blind index of the raw token is ever
 * written to the database — the raw token exists only inside the emailed
 * link, briefly, and is never logged.
 */
final class PasswordResetRepo
{
    public function create(int $userId, string $token, ?string $ipHash, int $ttlSeconds = 3600): int
    {
        // Burn any earlier outstanding token for this user first — only the
        // most recent reset link should ever work.
        Db::exec(
            'UPDATE password_resets SET consumed_at = NOW() WHERE user_id = ? AND consumed_at IS NULL',
            [$userId]
        );

        return Db::insert(
            'INSERT INTO password_resets (user_id, token_hash, ip_hash, expires_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))',
            [$userId, self::hash($token), $ipHash, $ttlSeconds]
        );
    }

    public function findValidByToken(string $token): ?array
    {
        return Db::first(
            'SELECT id, user_id FROM password_resets
              WHERE token_hash = ? AND consumed_at IS NULL AND expires_at > NOW()',
            [self::hash($token)]
        );
    }

    public function consume(int $id): void
    {
        Db::exec('UPDATE password_resets SET consumed_at = NOW() WHERE id = ?', [$id]);
    }

    private static function hash(string $token): string
    {
        return Crypto::blindIndex($token);
    }
}
