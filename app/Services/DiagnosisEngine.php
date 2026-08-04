<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;

/**
 * Weighted symptom scoring (spec §8.3).
 *
 * Confidence = matched weight ÷ total weight for that problem, so a problem
 * that has ten symptoms is not unfairly favoured over one that has three.
 */
final class DiagnosisEngine
{
    public const DISCLAIMER = 'এই ফলাফল সাধারণ লক্ষণের ভিত্তিতে তৈরি, নিশ্চিত রোগ নির্ণয় নয়। '
        . 'রাসায়নিক ব্যবহারের আগে মাত্রা ভালোভাবে দেখে নিন এবং শিশু ও পোষা প্রাণী থেকে দূরে রাখুন।';

    /**
     * @param int[] $symptomIds
     * @return array<int,array{id:int,slug:string,name_bn:string,type:string,severity:string,
     *                         score:float,matched:int,confidence:float,confidence_label:string}>
     */
    public function diagnose(array $symptomIds, ?int $plantId = null, int $limit = 5): array
    {
        $symptomIds = array_values(array_unique(array_filter(array_map('intval', $symptomIds))));

        if ($symptomIds === []) {
            return [];
        }

        $in     = Db::placeholders($symptomIds);
        $params = $symptomIds;
        $params[] = $plantId;
        $params[] = $plantId;

        $rows = Db::all(
            'SELECT p.id, p.slug, p.name_bn, p.type, p.severity,
                    SUM(ps.weight)          AS score,
                    COUNT(ps.symptom_id)    AS matched,
                    (SELECT SUM(weight) FROM problem_symptoms WHERE problem_id = p.id) AS total_weight
               FROM problems p
               JOIN problem_symptoms ps ON ps.problem_id = p.id
              WHERE ps.symptom_id IN (' . $in . ')
                AND p.is_published = 1
                AND (? IS NULL OR EXISTS (
                      SELECT 1 FROM plant_problems pp
                       WHERE pp.problem_id = p.id AND pp.plant_id = ?))
              GROUP BY p.id, p.slug, p.name_bn, p.type, p.severity
              ORDER BY score DESC, matched DESC, FIELD(p.severity,"high","medium","low")
              LIMIT ' . (int) $limit,
            $params
        );

        foreach ($rows as &$row) {
            $total = max(1.0, (float) $row['total_weight']);
            $confidence = min(1.0, (float) $row['score'] / $total);

            $row['score']            = (float) $row['score'];
            $row['matched']          = (int) $row['matched'];
            $row['confidence']       = $confidence;
            $row['confidence_label'] = self::label($confidence);
        }
        unset($row);

        return $rows;
    }

    public static function label(float $confidence): string
    {
        return match (true) {
            $confidence >= 0.7 => 'সম্ভাবনা বেশি',
            $confidence >= 0.4 => 'হতে পারে',
            default            => 'কম সম্ভাবনা',
        };
    }

    public static function labelClass(float $confidence): string
    {
        return match (true) {
            $confidence >= 0.7 => 'high',
            $confidence >= 0.4 => 'medium',
            default            => 'low',
        };
    }

    /** Validate submitted symptom ids against the table before scoring. */
    public function validSymptomIds(array $candidateIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $candidateIds))));
        if ($ids === []) {
            return [];
        }

        $rows = Db::all(
            'SELECT id FROM symptoms WHERE id IN (' . Db::placeholders($ids) . ')',
            $ids
        );

        return array_map(static fn(array $r): int => (int) $r['id'], $rows);
    }

    /** @param int[] $symptomIds */
    public function namesFor(array $symptomIds): array
    {
        if ($symptomIds === []) {
            return [];
        }
        return Db::all(
            'SELECT id, name_bn, body_part FROM symptoms
              WHERE id IN (' . Db::placeholders($symptomIds) . ')
              ORDER BY sort_order',
            $symptomIds
        );
    }
}
