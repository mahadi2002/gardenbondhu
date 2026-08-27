<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class UserRepo
{
    private const COLUMNS = 'id, email, display_name, district,
                             role, status, locale, created_at, last_login_at, anonymized_at';

    public function find(int $id): ?array
    {
        return Db::first('SELECT ' . self::COLUMNS . ' FROM users WHERE id = ?', [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return Db::first('SELECT ' . self::COLUMNS . ' FROM users WHERE email = ?', [$email]);
    }

    /** Only the login flow needs the password hash — never selected otherwise. */
    public function findForAuth(string $email): ?array
    {
        return Db::first('SELECT id, email, password_hash, status FROM users WHERE email = ?', [$email]);
    }

    public function create(string $email, string $passwordHash): array
    {
        $id = Db::insert(
            'INSERT INTO users (email, password_hash, status) VALUES (?, ?, "active")',
            [$email, $passwordHash]
        );

        return (array) $this->find($id);
    }

    public function updatePassword(int $userId, string $passwordHash): void
    {
        Db::exec('UPDATE users SET password_hash = ? WHERE id = ?', [$passwordHash, $userId]);
    }

    public function touchLogin(int $userId): void
    {
        Db::exec('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$userId]);
    }

    public function updateProfile(int $userId, ?string $displayName, ?string $district): void
    {
        Db::exec(
            'UPDATE users SET display_name = ?, district = ? WHERE id = ?',
            [$displayName ?: null, $district ?: null, $userId]
        );
    }

    public function setStatus(int $userId, string $status): void
    {
        Db::exec('UPDATE users SET status = ? WHERE id = ?', [$status, $userId]);
    }

    public function setRole(int $userId, string $role): void
    {
        Db::exec('UPDATE users SET role = ? WHERE id = ?', [$role, $userId]);
    }

    /**
     * Irreversibly strip the identifiers while keeping the row, so foreign
     * keys stay intact — this is how "delete my account" actually gets
     * implemented without breaking every table that references it.
     */
    public function anonymize(int $userId): void
    {
        Db::exec(
            'UPDATE users
                SET email = CONCAT("anon+", id, "-", SUBSTRING(SHA2(UUID(), 256), 1, 12), "@deleted.invalid"),
                    password_hash = "",
                    display_name = NULL,
                    district = NULL,
                    status = "blocked",
                    anonymized_at = NOW()
              WHERE id = ? AND anonymized_at IS NULL',
            [$userId]
        );
    }

    /** Admin search — by email substring. */
    public function searchByEmail(string $q, int $limit = 50): array
    {
        return Db::all(
            'SELECT ' . self::COLUMNS . ' FROM users WHERE email LIKE ?
              ORDER BY id DESC LIMIT ' . (int) $limit,
            ['%' . $q . '%']
        );
    }

    public function recent(int $limit = 50): array
    {
        return Db::all(
            'SELECT ' . self::COLUMNS . ' FROM users ORDER BY id DESC LIMIT ' . (int) $limit
        );
    }

    public function countByStatus(string $status): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM users WHERE status = ?', [$status]);
    }

    public function countNewSince(int $days): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)',
            [$days]
        );
    }
}
