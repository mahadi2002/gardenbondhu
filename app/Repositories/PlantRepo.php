<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class PlantRepo
{
    /** Columns safe to send to a non-subscriber. body_bn is deliberately absent. */
    private const TEASER_COLUMNS = 'p.id, p.slug, p.name_bn, p.name_en, p.scientific_name, p.category_id,
        p.difficulty, p.space_type, p.sunlight, p.water_need, p.growth_habit, p.pot_size_cm,
        p.toxic_to_pets, p.summary_bn, p.hero_image, p.view_count, p.harvest_days';

    public function findBySlug(string $slug, bool $withBody): ?array
    {
        $columns = self::TEASER_COLUMNS . ($withBody
            ? ', p.body_bn, p.soil_mix, p.fertilizer_note, p.pruning_note, p.propagation,
                 p.water_interval_days, p.fertilizer_interval_days, p.temp_min_c, p.temp_max_c,
                 p.ph_min, p.ph_max, p.local_names'
            : '');

        return Db::first(
            'SELECT ' . $columns . ', c.name_bn AS category_bn, c.slug AS category_slug
               FROM plants p
               LEFT JOIN plant_categories c ON c.id = p.category_id
              WHERE p.slug = ? AND p.is_published = 1',
            [$slug]
        );
    }

    public function findById(int $id): ?array
    {
        return Db::first('SELECT * FROM plants WHERE id = ?', [$id]);
    }

    /**
     * Plant finder (spec §8.1). Every filter is a WHERE clause on an indexed
     * column, bound as a parameter — nothing is interpolated.
     *
     * @param array{space_type?:string, sunlight?:string, water_need?:string,
     *              difficulty?:string, category_id?:int, toxic_to_pets?:string, q?:string} $filters
     */
    public function filter(array $filters, string $sort = 'name_bn', int $limit = 60, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        $allowed = (array) config('content.sortable.plants', ['name_bn']);
        $sortCol = in_array($sort, $allowed, true) ? $sort : 'name_bn';
        $dir     = in_array($sortCol, ['created_at', 'view_count'], true) ? 'DESC' : 'ASC';

        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . ', c.name_bn AS category_bn
               FROM plants p
               LEFT JOIN plant_categories c ON c.id = p.category_id
              WHERE ' . $where . '
              ORDER BY p.' . $sortCol . ' ' . $dir . '
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        return (int) Db::value('SELECT COUNT(*) FROM plants p WHERE ' . $where, $params);
    }

    /** @return array{0:string, 1:array} */
    private function buildWhere(array $filters): array
    {
        $where  = ['p.is_published = 1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int) $filters['category_id'];
        }
        if (!empty($filters['sunlight']) && isset(config('content.sunlight')[$filters['sunlight']])) {
            $where[]  = 'p.sunlight = ?';
            $params[] = (string) $filters['sunlight'];
        }
        if (!empty($filters['water_need']) && isset(config('content.water')[$filters['water_need']])) {
            $where[]  = 'p.water_need = ?';
            $params[] = (string) $filters['water_need'];
        }
        if (!empty($filters['difficulty']) && isset(config('content.difficulty')[$filters['difficulty']])) {
            $where[]  = 'p.difficulty = ?';
            $params[] = (string) $filters['difficulty'];
        }
        if (!empty($filters['space_type']) && isset(config('content.space')[$filters['space_type']])) {
            $where[]  = 'FIND_IN_SET(?, p.space_type) > 0';
            $params[] = (string) $filters['space_type'];
        }
        if (isset($filters['toxic_to_pets']) && $filters['toxic_to_pets'] === '0') {
            $where[] = 'p.toxic_to_pets = 0';
        }
        if (!empty($filters['q'])) {
            $where[]  = '(p.name_bn LIKE ? OR p.name_en LIKE ? OR p.scientific_name LIKE ?)';
            $like     = '%' . $filters['q'] . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        return [implode(' AND ', $where), $params];
    }

    public function categories(): array
    {
        return Db::all(
            'SELECT c.id, c.slug, c.name_bn, c.name_en, c.sort_order,
                    (SELECT COUNT(*) FROM plants p WHERE p.category_id = c.id AND p.is_published = 1) AS plant_count
               FROM plant_categories c
              ORDER BY c.sort_order, c.id'
        );
    }

    public function seasonsFor(int $plantId): array
    {
        return Db::all(
            'SELECT ps.season_id, ps.activity, ps.note_bn, s.name_bn, s.bn_months, s.month_start, s.month_end
               FROM plant_seasons ps
               JOIN seasons s ON s.id = ps.season_id
              WHERE ps.plant_id = ?
              ORDER BY s.id',
            [$plantId]
        );
    }

    public function problemsFor(int $plantId): array
    {
        return Db::all(
            'SELECT pr.id, pr.slug, pr.name_bn, pr.type, pr.severity, pp.frequency
               FROM plant_problems pp
               JOIN problems pr ON pr.id = pp.problem_id AND pr.is_published = 1
              WHERE pp.plant_id = ?
              ORDER BY FIELD(pp.frequency, "common","occasional","rare"),
                       FIELD(pr.severity, "high","medium","low")',
            [$plantId]
        );
    }

    public function related(int $plantId, ?int $categoryId, int $limit = 4): array
    {
        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM plants p
              WHERE p.is_published = 1 AND p.id <> ?
                AND (? IS NULL OR p.category_id = ?)
              ORDER BY p.view_count DESC
              LIMIT ' . (int) $limit,
            [$plantId, $categoryId, $categoryId]
        );
    }

    public function popular(int $limit = 6): array
    {
        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM plants p
              WHERE p.is_published = 1
              ORDER BY p.view_count DESC, p.id
              LIMIT ' . (int) $limit
        );
    }

    /** Beginner-friendly picks for the dashboard. */
    public function beginnerPicks(int $limit = 6): array
    {
        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM plants p
              WHERE p.is_published = 1 AND p.difficulty = "easy"
              ORDER BY p.view_count DESC, p.id
              LIMIT ' . (int) $limit
        );
    }

    public function incrementViews(int $plantId): void
    {
        Db::exec('UPDATE plants SET view_count = view_count + 1 WHERE id = ?', [$plantId]);
    }

    public function options(): array
    {
        return Db::all('SELECT id, name_bn FROM plants WHERE is_published = 1 ORDER BY name_bn');
    }

    public function publishedCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM plants WHERE is_published = 1');
    }

    public function slugsForSitemap(): array
    {
        return Db::all('SELECT slug, updated_at FROM plants WHERE is_published = 1 ORDER BY id');
    }

    // ── admin ───────────────────────────────────────────────────────────

    public function adminList(string $q = '', int $limit = 100): array
    {
        if ($q !== '') {
            return Db::all(
                'SELECT id, slug, name_bn, difficulty, is_published, view_count, updated_at
                   FROM plants WHERE name_bn LIKE ? OR slug LIKE ? OR name_en LIKE ?
                  ORDER BY id DESC LIMIT ' . (int) $limit,
                ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%']
            );
        }
        return Db::all(
            'SELECT id, slug, name_bn, difficulty, is_published, view_count, updated_at
               FROM plants ORDER BY id DESC LIMIT ' . (int) $limit
        );
    }

    public function save(?int $id, array $data): int
    {
        $fields = [
            'slug', 'name_bn', 'name_en', 'scientific_name', 'category_id', 'difficulty',
            'space_type', 'sunlight', 'water_need', 'growth_habit', 'soil_mix', 'pot_size_cm',
            'water_interval_days', 'fertilizer_interval_days', 'propagation', 'fertilizer_note',
            'pruning_note', 'harvest_days', 'toxic_to_pets', 'summary_bn', 'body_bn',
            'hero_image', 'is_published',
        ];

        $params = [];
        foreach ($fields as $field) {
            $params[] = $data[$field] ?? null;
        }

        if ($id === null) {
            return Db::insert(
                'INSERT INTO plants (' . implode(',', $fields) . ')
                 VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')',
                $params
            );
        }

        $set = implode(' = ?, ', $fields) . ' = ?';
        $params[] = $id;
        Db::exec('UPDATE plants SET ' . $set . ' WHERE id = ?', $params);

        return $id;
    }

    public function delete(int $id): void
    {
        Db::exec('DELETE FROM plants WHERE id = ?', [$id]);
    }
}
