<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;

/**
 * Generates care_tasks for the coming week.
 *
 * Runs daily from cron for everybody, and once inline when a plant is first
 * added — so the user sees a schedule immediately rather than tomorrow.
 */
final class CareScheduler
{
    private const HORIZON_DAYS = 7;

    /** Baseline watering interval in days when the plant record has none. */
    private const WATER_INTERVAL = ['low' => 7, 'medium' => 3, 'high' => 2];

    /** Monsoon and winter shift the whole schedule; this is the multiplier. */
    private const SEASON_FACTOR = [
        1 => 0.8,   // গ্রীষ্ম — hotter, water more often
        2 => 1.6,   // বর্ষা  — rain does the work
        3 => 1.1,   // শরৎ
        4 => 1.2,   // হেমন্ত
        5 => 1.4,   // শীত   — growth slows
        6 => 1.0,   // বসন্ত
    ];

    public function generateForUser(int $userId): int
    {
        $plants = Db::all(
            'SELECT up.id, up.location, up.last_watered_at, up.last_fertilized_at, up.pot_size_cm,
                    p.water_need, p.water_interval_days, p.fertilizer_interval_days
               FROM user_plants up
               LEFT JOIN plants p ON p.id = up.plant_id
              WHERE up.user_id = ? AND up.is_archived = 0',
            [$userId]
        );

        $created = 0;
        foreach ($plants as $plant) {
            $created += $this->generateForPlant($plant);
        }
        return $created;
    }

    public function generateForAll(int $batch = 500): int
    {
        $created = 0;
        $lastId  = 0;

        while (true) {
            $plants = Db::all(
                'SELECT up.id, up.location, up.last_watered_at, up.last_fertilized_at, up.pot_size_cm,
                        p.water_need, p.water_interval_days, p.fertilizer_interval_days
                   FROM user_plants up
                   LEFT JOIN plants p ON p.id = up.plant_id
                  WHERE up.is_archived = 0 AND up.id > ?
                  ORDER BY up.id
                  LIMIT ' . (int) $batch,
                [$lastId]
            );

            if ($plants === []) {
                break;
            }

            foreach ($plants as $plant) {
                $created += $this->generateForPlant($plant);
                $lastId   = (int) $plant['id'];
            }
        }

        return $created;
    }

    /** @param array{id:int,water_need:?string,water_interval_days:?int,...} $plant */
    public function generateForPlant(array $plant): int
    {
        $userPlantId = (int) $plant['id'];
        $season      = self::currentSeasonId();
        $factor      = self::SEASON_FACTOR[$season] ?? 1.0;

        $waterEvery = (int) round(
            (int) ($plant['water_interval_days'] ?: self::WATER_INTERVAL[(string) ($plant['water_need'] ?? 'medium')] ?? 3)
            * $factor
        );
        $waterEvery = max(1, min(21, $waterEvery));

        // Indoor pots dry out more slowly than a rooftop in full sun.
        if (($plant['location'] ?? '') === 'indoor') {
            $waterEvery = min(21, $waterEvery + 1);
        } elseif (($plant['location'] ?? '') === 'rooftop' && $season === 1) {
            $waterEvery = max(1, $waterEvery - 1);
        }

        $fertEvery = max(7, (int) ($plant['fertilizer_interval_days'] ?: 21));

        $created = 0;
        $created += $this->schedule($userPlantId, 'water', $plant['last_watered_at'] ?? null, $waterEvery);
        $created += $this->schedule($userPlantId, 'fertilize', $plant['last_fertilized_at'] ?? null, $fertEvery);

        return $created;
    }

    /**
     * Insert every due date from `$last + $interval` up to the horizon.
     * The UNIQUE(user_plant_id, task, due_on) key makes this idempotent, so
     * the cron can run twice a day without duplicating anything.
     */
    private function schedule(int $userPlantId, string $task, ?string $lastDone, int $intervalDays): int
    {
        $today = strtotime('today');
        $start = $lastDone !== null ? strtotime($lastDone) : $today;
        $due   = $start + ($intervalDays * 86400);

        // Overdue: bring the next occurrence forward to today rather than
        // backfilling a month of missed tasks the user cannot act on.
        if ($due < $today) {
            $due = $today;
        }

        $horizon = $today + (self::HORIZON_DAYS * 86400);
        $created = 0;

        while ($due <= $horizon) {
            $created += Db::exec(
                'INSERT IGNORE INTO care_tasks (user_plant_id, task, due_on, auto_generated)
                 VALUES (?, ?, ?, 1)',
                [$userPlantId, $task, date('Y-m-d', $due)]
            );
            $due += $intervalDays * 86400;
        }

        return $created;
    }

    /** Bangla season for a Gregorian month (seasons table id). */
    public static function currentSeasonId(?int $month = null): int
    {
        $month ??= (int) date('n');

        return match (true) {
            $month >= 4  && $month <= 5  => 1,   // গ্রীষ্ম
            $month >= 6  && $month <= 7  => 2,   // বর্ষা
            $month >= 8  && $month <= 9  => 3,   // শরৎ
            $month >= 10 && $month <= 11 => 4,   // হেমন্ত
            $month === 12 || $month === 1 => 5,  // শীত
            default                       => 6,  // বসন্ত
        };
    }

    /** Everything worth doing in the garden this month, across all plants. */
    public static function monthlyActivities(int $month): array
    {
        $season = self::currentSeasonId($month);

        return Db::all(
            'SELECT ps.activity, ps.note_bn, p.id, p.slug, p.name_bn, p.hero_image
               FROM plant_seasons ps
               JOIN plants p ON p.id = ps.plant_id AND p.is_published = 1
              WHERE ps.season_id = ?
              ORDER BY FIELD(ps.activity,"sow","plant","fertilize","prune","harvest","repot","protect"), p.name_bn',
            [$season]
        );
    }
}
