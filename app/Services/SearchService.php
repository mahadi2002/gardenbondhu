<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;

/**
 * LIKE '%term%' search across plants, problems, and guides.
 *
 * MySQL 8's ngram parser could tokenise Bangla for real FULLTEXT search;
 * MariaDB never shipped it, so this always ran the LIKE fallback in
 * practice, and Postgres has no ngram-equivalent either — the project
 * decision (see migration history) is to keep LIKE permanently rather than
 * take on tsvector/pg_trgm for it. Fine at this content volume (60 plants /
 * 40 problems / 20 guides).
 */
final class SearchService
{
    /**
     * @return array{plants:array, problems:array, guides:array, total:int}
     */
    public function search(string $query, int $perType = 8): array
    {
        $query = trim(preg_replace('/\s+/u', ' ', $query) ?? '');

        if (mb_strlen($query, 'UTF-8') < 2) {
            return ['plants' => [], 'problems' => [], 'guides' => [], 'total' => 0];
        }

        $plants   = $this->searchPlants($query, $perType);
        $problems = $this->searchProblems($query, $perType);
        $guides   = $this->searchGuides($query, $perType);

        return [
            'plants'   => $plants,
            'problems' => $problems,
            'guides'   => $guides,
            'total'    => count($plants) + count($problems) + count($guides),
        ];
    }

    private function searchPlants(string $q, int $limit): array
    {
        // Columns must match what partials/plant-card.php renders (sunlight,
        // water_need, difficulty chips) — search results use the same card
        // partial as every other plant listing.
        $like = '%' . $q . '%';
        return Db::all(
            'SELECT id, slug, name_bn, name_en, summary_bn, hero_image, difficulty, sunlight, water_need
               FROM plants
              WHERE is_published = 1
                AND (name_bn LIKE ? OR name_en LIKE ? OR summary_bn LIKE ? OR scientific_name LIKE ?)
              ORDER BY name_bn
              LIMIT ' . (int) $limit,
            [$like, $like, $like, $like]
        );
    }

    private function searchProblems(string $q, int $limit): array
    {
        $like = '%' . $q . '%';
        return Db::all(
            "SELECT id, slug, name_bn, name_en, description_bn, type, severity
               FROM problems
              WHERE is_published = 1 AND (name_bn LIKE ? OR name_en LIKE ? OR description_bn LIKE ?)
              ORDER BY FIELD(severity, 'high','medium','low')
              LIMIT " . (int) $limit,
            [$like, $like, $like]
        );
    }

    private function searchGuides(string $q, int $limit): array
    {
        $like = '%' . $q . '%';
        return Db::all(
            'SELECT id, slug, title_bn, excerpt_bn, category, read_minutes
               FROM guides
              WHERE is_published = 1 AND (title_bn LIKE ? OR excerpt_bn LIKE ?)
              ORDER BY published_at DESC
              LIMIT ' . (int) $limit,
            [$like, $like]
        );
    }

    /** Something to offer when a search comes back empty. */
    public function suggestions(int $limit = 6): array
    {
        return Db::all(
            'SELECT slug, name_bn FROM plants WHERE is_published = 1
              ORDER BY view_count DESC, id LIMIT ' . (int) $limit
        );
    }
}
