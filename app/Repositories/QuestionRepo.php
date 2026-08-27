<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class QuestionRepo
{
    /** Approved and answered questions are the public feed; pending are not. */
    public function feed(int $limit = 30, int $offset = 0, string $q = ''): array
    {
        $where  = 'q.status IN ("approved","answered")';
        $params = [];

        if ($q !== '') {
            $where   .= ' AND (q.title LIKE ? OR q.body LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        return Db::all(
            'SELECT q.id, q.title, q.body, q.image, q.status, q.answer_count, q.created_at,
                    u.display_name, u.role,
                    p.name_bn AS plant_name_bn
               FROM questions q
               JOIN users u ON u.id = q.user_id
               LEFT JOIN plants p ON p.id = q.plant_id
              WHERE ' . $where . '
              ORDER BY q.created_at DESC
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    /** The asker can always see their own question, even while pending. */
    public function find(int $id, ?int $viewerId = null): ?array
    {
        return Db::first(
            'SELECT q.*, u.display_name, u.role, p.name_bn AS plant_name_bn, p.slug AS plant_slug
               FROM questions q
               JOIN users u ON u.id = q.user_id
               LEFT JOIN plants p ON p.id = q.plant_id
              WHERE q.id = ? AND (q.status IN ("approved","answered") OR q.user_id <=> ?)',
            [$id, $viewerId]
        );
    }

    public function findForAdmin(int $id): ?array
    {
        return Db::first(
            'SELECT q.*, u.email, u.role FROM questions q
               JOIN users u ON u.id = q.user_id WHERE q.id = ?',
            [$id]
        );
    }

    public function create(int $userId, array $data): int
    {
        return Db::insert(
            'INSERT INTO questions (user_id, plant_id, title, body, image, status)
             VALUES (?, ?, ?, ?, ?, "pending")',
            [$userId, $data['plant_id'] ?: null, $data['title'], $data['body'], $data['image'] ?: null]
        );
    }

    public function answers(int $questionId): array
    {
        return Db::all(
            'SELECT a.id, a.body, a.is_expert, a.is_accepted, a.upvotes, a.created_at,
                    u.display_name, u.role, ad.name AS admin_name
               FROM answers a
               LEFT JOIN users u ON u.id = a.user_id
               LEFT JOIN admins ad ON ad.id = a.admin_id
              WHERE a.question_id = ?
              ORDER BY a.is_accepted DESC, a.is_expert DESC, a.created_at',
            [$questionId]
        );
    }

    public function addAnswer(int $questionId, ?int $userId, ?int $adminId, string $body, bool $isExpert): int
    {
        return Db::transaction(static function () use ($questionId, $userId, $adminId, $body, $isExpert): int {
            $id = Db::insert(
                'INSERT INTO answers (question_id, user_id, admin_id, body, is_expert)
                 VALUES (?, ?, ?, ?, ?)',
                [$questionId, $userId, $adminId, $body, $isExpert ? 1 : 0]
            );

            Db::exec(
                'UPDATE questions
                    SET answer_count = answer_count + 1,
                        status = IF(status = "approved", "answered", status)
                  WHERE id = ?',
                [$questionId]
            );

            return $id;
        });
    }

    public function setStatus(int $id, string $status): void
    {
        Db::exec('UPDATE questions SET status = ? WHERE id = ?', [$status, $id]);
    }

    public function askedTodayBy(int $userId): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM questions WHERE user_id = ? AND created_at >= CURDATE()',
            [$userId]
        );
    }

    public function moderationQueue(string $status = 'pending', int $limit = 50): array
    {
        return Db::all(
            'SELECT q.id, q.title, q.status, q.answer_count, q.created_at, q.image, u.email
               FROM questions q JOIN users u ON u.id = q.user_id
              WHERE q.status = ?
              ORDER BY q.created_at
              LIMIT ' . (int) $limit,
            [$status]
        );
    }

    public function pendingCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM questions WHERE status = "pending"');
    }
}
