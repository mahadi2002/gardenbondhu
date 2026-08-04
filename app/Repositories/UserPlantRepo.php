<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

/**
 * Every method takes $userId and scopes the query by it. A row belonging to
 * someone else is simply not found — the controller turns that into a 404,
 * never a 403, so existence is not confirmed (spec §10).
 */
final class UserPlantRepo
{
    public function forUser(int $userId, bool $includeArchived = false): array
    {
        return Db::all(
            'SELECT up.*, p.name_bn AS plant_name_bn, p.slug AS plant_slug, p.water_need,
                    p.water_interval_days, p.hero_image
               FROM user_plants up
               LEFT JOIN plants p ON p.id = up.plant_id
              WHERE up.user_id = ?' . ($includeArchived ? '' : ' AND up.is_archived = 0') . '
              ORDER BY up.created_at DESC',
            [$userId]
        );
    }

    public function find(int $id, int $userId): ?array
    {
        return Db::first(
            'SELECT up.*, p.name_bn AS plant_name_bn, p.slug AS plant_slug, p.water_need,
                    p.water_interval_days, p.fertilizer_interval_days, p.sunlight, p.soil_mix,
                    p.hero_image
               FROM user_plants up
               LEFT JOIN plants p ON p.id = up.plant_id
              WHERE up.id = ? AND up.user_id = ?',
            [$id, $userId]
        );
    }

    public function create(int $userId, array $data): int
    {
        return Db::insert(
            'INSERT INTO user_plants (user_id, plant_id, nickname, planted_on, location, pot_size_cm, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $userId,
                $data['plant_id'] ?: null,
                $data['nickname'] ?: null,
                $data['planted_on'] ?: null,
                $data['location'],
                $data['pot_size_cm'] ?: null,
                $data['notes'] ?: null,
            ]
        );
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return Db::exec(
            'UPDATE user_plants
                SET nickname = ?, planted_on = ?, location = ?, pot_size_cm = ?, notes = ?, is_archived = ?
              WHERE id = ? AND user_id = ?',
            [
                $data['nickname'] ?: null,
                $data['planted_on'] ?: null,
                $data['location'],
                $data['pot_size_cm'] ?: null,
                $data['notes'] ?: null,
                (int) ($data['is_archived'] ?? 0),
                $id,
                $userId,
            ]
        ) > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return Db::exec('DELETE FROM user_plants WHERE id = ? AND user_id = ?', [$id, $userId]) > 0;
    }

    public function countForUser(int $userId): int
    {
        return (int) Db::value(
            'SELECT COUNT(*) FROM user_plants WHERE user_id = ? AND is_archived = 0',
            [$userId]
        );
    }

    // ── care tasks ──────────────────────────────────────────────────────

    public function tasksForUser(int $userId, string $from, string $to): array
    {
        return Db::all(
            'SELECT t.id, t.task, t.due_on, t.done_at, t.user_plant_id,
                    COALESCE(up.nickname, p.name_bn, "গাছ") AS plant_label
               FROM care_tasks t
               JOIN user_plants up ON up.id = t.user_plant_id
               LEFT JOIN plants p ON p.id = up.plant_id
              WHERE up.user_id = ? AND up.is_archived = 0
                AND t.due_on BETWEEN ? AND ?
              ORDER BY t.due_on, FIELD(t.task,"water","fertilize","spray","prune","repot","check")',
            [$userId, $from, $to]
        );
    }

    public function tasksForPlant(int $userPlantId, int $userId): array
    {
        return Db::all(
            'SELECT t.id, t.task, t.due_on, t.done_at
               FROM care_tasks t
               JOIN user_plants up ON up.id = t.user_plant_id
              WHERE t.user_plant_id = ? AND up.user_id = ?
              ORDER BY t.due_on DESC
              LIMIT 30',
            [$userPlantId, $userId]
        );
    }

    /**
     * Complete a task. The join on user_plants is what scopes it to the owner;
     * another user's task id simply affects zero rows.
     */
    public function completeTask(int $taskId, int $userId): ?array
    {
        $task = Db::first(
            'SELECT t.id, t.task, t.user_plant_id
               FROM care_tasks t
               JOIN user_plants up ON up.id = t.user_plant_id
              WHERE t.id = ? AND up.user_id = ? AND t.done_at IS NULL',
            [$taskId, $userId]
        );

        if ($task === null) {
            return null;
        }

        Db::exec('UPDATE care_tasks SET done_at = NOW() WHERE id = ?', [$taskId]);

        if ($task['task'] === 'water') {
            Db::exec('UPDATE user_plants SET last_watered_at = CURDATE() WHERE id = ?', [$task['user_plant_id']]);
        } elseif ($task['task'] === 'fertilize') {
            Db::exec('UPDATE user_plants SET last_fertilized_at = CURDATE() WHERE id = ?', [$task['user_plant_id']]);
        }

        return $task;
    }

    public function dueTodayCount(int $userId): int
    {
        return (int) Db::value(
            'SELECT COUNT(*)
               FROM care_tasks t
               JOIN user_plants up ON up.id = t.user_plant_id
              WHERE up.user_id = ? AND t.done_at IS NULL AND t.due_on <= CURDATE()',
            [$userId]
        );
    }
}
