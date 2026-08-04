<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Core\Db;

final class GuideRepo
{
    private const TEASER_COLUMNS = 'g.id, g.slug, g.title_bn, g.category, g.excerpt_bn,
        g.cover_image, g.read_minutes, g.is_premium, g.published_at, g.view_count';

    public function findBySlug(string $slug, bool $withBody): ?array
    {
        $columns = self::TEASER_COLUMNS . ($withBody ? ', g.body_bn' : '');

        return Db::first(
            'SELECT ' . $columns . ' FROM guides g WHERE g.slug = ? AND g.is_published = 1',
            [$slug]
        );
    }

    public function findById(int $id): ?array
    {
        return Db::first('SELECT * FROM guides WHERE id = ?', [$id]);
    }

    public function filter(array $filters, int $limit = 40, int $offset = 0): array
    {
        [$where, $params] = $this->buildWhere($filters);

        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM guides g
              WHERE ' . $where . '
              ORDER BY g.published_at DESC, g.id DESC
              LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset,
            $params
        );
    }

    public function countFiltered(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);
        return (int) Db::value('SELECT COUNT(*) FROM guides g WHERE ' . $where, $params);
    }

    /** @return array{0:string, 1:array} */
    private function buildWhere(array $filters): array
    {
        $where  = ['g.is_published = 1'];
        $params = [];

        if (!empty($filters['category']) && isset(config('content.guide_category')[$filters['category']])) {
            $where[]  = 'g.category = ?';
            $params[] = (string) $filters['category'];
        }
        if (!empty($filters['q'])) {
            $where[]  = '(g.title_bn LIKE ? OR g.excerpt_bn LIKE ?)';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }

        return [implode(' AND ', $where), $params];
    }

    public function latest(int $limit = 4): array
    {
        return Db::all(
            'SELECT ' . self::TEASER_COLUMNS . '
               FROM guides g WHERE g.is_published = 1
              ORDER BY g.published_at DESC, g.id DESC
              LIMIT ' . (int) $limit
        );
    }

    public function incrementViews(int $id): void
    {
        Db::exec('UPDATE guides SET view_count = view_count + 1 WHERE id = ?', [$id]);
    }

    public function publishedCount(): int
    {
        return (int) Db::value('SELECT COUNT(*) FROM guides WHERE is_published = 1');
    }

    public function slugsForSitemap(): array
    {
        return Db::all('SELECT slug, updated_at FROM guides WHERE is_published = 1 ORDER BY id');
    }

    // ── admin ───────────────────────────────────────────────────────────

    public function adminList(string $q = '', int $limit = 100): array
    {
        if ($q !== '') {
            return Db::all(
                'SELECT id, slug, title_bn, category, is_premium, is_published, published_at
                   FROM guides WHERE title_bn LIKE ? OR slug LIKE ?
                  ORDER BY id DESC LIMIT ' . (int) $limit,
                ['%' . $q . '%', '%' . $q . '%']
            );
        }
        return Db::all(
            'SELECT id, slug, title_bn, category, is_premium, is_published, published_at
               FROM guides ORDER BY id DESC LIMIT ' . (int) $limit
        );
    }

    public function save(?int $id, array $data): int
    {
        $fields = ['slug', 'title_bn', 'category', 'excerpt_bn', 'body_bn',
                   'read_minutes', 'is_premium', 'is_published', 'published_at'];

        $params = [];
        foreach ($fields as $field) {
            $params[] = $data[$field] ?? null;
        }

        if ($id === null) {
            return Db::insert(
                'INSERT INTO guides (' . implode(',', $fields) . ')
                 VALUES (' . implode(',', array_fill(0, count($fields), '?')) . ')',
                $params
            );
        }

        $params[] = $id;
        Db::exec('UPDATE guides SET ' . implode(' = ?, ', $fields) . ' = ? WHERE id = ?', $params);

        return $id;
    }

    public function delete(int $id): void
    {
        Db::exec('DELETE FROM guides WHERE id = ?', [$id]);
    }
}
