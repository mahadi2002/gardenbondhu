<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class ProblemRepo
{
    /** Free columns. The remedies are paid and are never selected for a teaser. */
    private const TEASER_COLUMNS = 'p.id, p.slug, p.name_bn, p.name_en, p.type, p.severity,
        p.description_bn, p.identification_bn';

    public function findBySlug(string $slug, bool $withRemedies): ?array
    {
        $columns = self::TEASER_COLUMNS . ($withRemedies
            ? ', p.organic_remedy_bn, p.chemical_remedy_bn, p.prevention_bn, p.safety_note_bn'
            : '');

        return Db::first(
            'SELECT ' . $columns . ' FROM problems p WHERE p.slug = ? AND p.is_published = 1',
            [$slug]
        );
    }

    public function findById(int $id): ?array
    {
        return Db::first('SELECT * FROM problems WHERE id = ?', [$id]);
    }

    public function filter(array $filters, int $limit = 60, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM problems p
              WHERE ' . $where . '
              ORDER BY FIELD(p.severity,"high","medium","low"), p.name_bn
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        return (int) Db::value('SELECT COUNT(*) FROM problems p WHERE ' . $where, $params);
    }

    /** @return array{0:string, 1:array} */
    private function buildWhere(array $filters): array
    {
        $where  = ['p.is_published = 1'];
        $params = [];

        if (!empty($filters['type']) && isset(config('content.problem_type')[$filters['type']])) {
            $where[]  = 'p.type = ?';
            $params[] = (string) $filters['type'];
        }
        if (!empty($filters['severity']) && isset(config('content.severity')[$filters['severity']])) {
            $where[]  = 'p.severity = ?';
            $params[] = (string) $filters['severity'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(p.name_bn LIKE ? OR p.name_en LIKE ? OR p.description_bn LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return [implode(' AND ', $where), $params];
    }

    /** All symptoms grouped by body part — drives the leaf picker. */
    public function symptomsByBodyPart(): array
    {
        $rows = Db::all('SELECT id, slug, name_bn, body_part, sort_order FROM symptoms ORDER BY sort_order, id');

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(string) $row['body_part']][] = $row;
        }
        return $grouped;
    }

    public function symptomsFor(int $problemId): array
    {
        return Db::all(
            'SELECT s.id, s.name_bn, s.body_part, ps.weight
               FROM problem_symptoms ps
               JOIN symptoms s ON s.id = ps.symptom_id
              WHERE ps.problem_id = ?
              ORDER BY ps.weight DESC',
            [$problemId]
        );
    }

    public function plantsFor(int $problemId, int $limit = 8): array
    {
        return Db::all(
            'SELECT p.id, p.slug, p.name_bn, pp.frequency
               FROM plant_problems pp
               JOIN plants p ON p.id = pp.plant_id AND p.is_published = 1
              WHERE pp.problem_id = ?
              ORDER BY FIELD(pp.frequency,"common","occasional","rare"), p.name_bn
              LIMIT ' . (int) $limit,
            [$problemId]
        );
    }

    public function publishedCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM problems WHERE is_published = 1');
    }

    public function slugsForSitemap(): array
    {
        return Db::all('SELECT slug, updated_at FROM problems WHERE is_published = 1 ORDER BY id');
    }

    public function common(int $limit = 6): array
    {
        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM problems p
              WHERE p.is_published = 1
              ORDER BY FIELD(p.severity,"high","medium","low"), p.id
              LIMIT ' . (int) $limit
        );
    }

    // ── admin ───────────────────────────────────────────────────────────

    public function adminList(string $q = '', int $limit = 100): array
    {
        if ($q !== '') {
            return Db::all(
                'SELECT id, slug, name_bn, type, severity, is_published, updated_at
                   FROM problems WHERE name_bn LIKE ? OR slug LIKE ?
                  ORDER BY id DESC LIMIT ' . (int) $limit,
                ['%' . $q . '%', '%' . $q . '%']
            );
        }
        return Db::all(
            'SELECT id, slug, name_bn, type, severity, is_published, updated_at
               FROM problems ORDER BY id DESC LIMIT ' . (int) $limit
        );
    }

    public function save(?int $id, array $data): int
    {
        $fields = [
            'slug', 'name_bn', 'name_en', 'type', 'severity', 'description_bn',
            'identification_bn', 'organic_remedy_bn', 'chemical_remedy_bn',
            'prevention_bn', 'safety_note_bn', 'is_published',
        ];

        $params = [];
        foreach ($fields as $field) {
            $params[] = $data[$field] ?? null;
        }

        if ($id === null) {
            return Db::insert(
                'INSERT INTO problems (' . implode(',', $fields) . ')
                 VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')',
                $params
            );
        }

        $params[] = $id;
        Db::exec('UPDATE problems SET ' . implode(' = ?, ', $fields) . ' = ? WHERE id = ?', $params);

        return $id;
    }

    /** Replace the symptom links for a problem, with weights 1–10. */
    public function syncSymptoms(int $problemId, array $weightsBySymptomId): void
    {
        Db::transaction(static function () use ($problemId, $weightsBySymptomId): void {
            Db::exec('DELETE FROM problem_symptoms WHERE problem_id = ?', [$problemId]);
            foreach ($weightsBySymptomId as $symptomId => $weight) {
                $weight = max(1, min(10, (int) $weight));
                Db::exec(
                    'INSERT INTO problem_symptoms (problem_id, symptom_id, weight) VALUES (?, ?, ?)',
                    [$problemId, (int) $symptomId, $weight]
                );
            }
        });
    }

    public function allSymptoms(): array
    {
        return Db::all('SELECT id, slug, name_bn, body_part FROM symptoms ORDER BY sort_order, id');
    }

    public function delete(int $id): void
    {
        Db::exec('DELETE FROM problems WHERE id = ?', [$id]);
    }
}
