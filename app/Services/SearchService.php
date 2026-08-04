<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Db;
use PDOException;

/**
 * FULLTEXT search with a LIKE fallback.
 *
 * MySQL 8 with the ngram parser tokenises Bangla properly. MariaDB has no
 * ngram parser, so 005_indexes.sql is skipped there and every query lands on
 * the LIKE path — which is perfectly adequate at 60 plants / 40 problems / 20
 * guides, and avoids pretending we have an index we do not.
 */
final class SearchService
{
    private static ?bool $fulltextAvailable = null;

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
        if ($this->fulltext()) {
            try {
                return Db::all(
                    'SELECT id, slug, name_bn, name_en, summary_bn, hero_image, difficulty,
                            MATCH(name_bn, name_en, summary_bn) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
                       FROM plants
                      WHERE is_published = 1
                        AND MATCH(name_bn, name_en, summary_bn) AGAINST (? IN NATURAL LANGUAGE MODE)
                      ORDER BY relevance DESC
                      LIMIT ' . (int) $limit,
                    [$q, $q]
                );
            } catch (PDOException) {
                self::$fulltextAvailable = false;
            }
        }

        $like = '%' . $q . '%';
        return Db::all(
            'SELECT id, slug, name_bn, name_en, summary_bn, hero_image, difficulty
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
        if ($this->fulltext()) {
            try {
                return Db::all(
                    'SELECT id, slug, name_bn, name_en, description_bn, type, severity,
                            MATCH(name_bn, name_en, description_bn) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
                       FROM problems
                      WHERE is_published = 1
                        AND MATCH(name_bn, name_en, description_bn) AGAINST (? IN NATURAL LANGUAGE MODE)
                      ORDER BY relevance DESC
                      LIMIT ' . (int) $limit,
                    [$q, $q]
                );
            } catch (PDOException) {
                self::$fulltextAvailable = false;
            }
        }

        $like = '%' . $q . '%';
        return Db::all(
            'SELECT id, slug, name_bn, name_en, description_bn, type, severity
               FROM problems
              WHERE is_published = 1 AND (name_bn LIKE ? OR name_en LIKE ? OR description_bn LIKE ?)
              ORDER BY FIELD(severity,"high","medium","low")
              LIMIT ' . (int) $limit,
            [$like, $like, $like]
        );
    }

    private function searchGuides(string $q, int $limit): array
    {
        if ($this->fulltext()) {
            try {
                return Db::all(
                    'SELECT id, slug, title_bn, excerpt_bn, category, read_minutes,
                            MATCH(title_bn, excerpt_bn) AGAINST (? IN NATURAL LANGUAGE MODE) AS relevance
                       FROM guides
                      WHERE is_published = 1
                        AND MATCH(title_bn, excerpt_bn) AGAINST (? IN NATURAL LANGUAGE MODE)
                      ORDER BY relevance DESC
                      LIMIT ' . (int) $limit,
                    [$q, $q]
                );
            } catch (PDOException) {
                self::$fulltextAvailable = false;
            }
        }

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

    /** Detected once per request from information_schema, then cached. */
    private function fulltext(): bool
    {
        if (self::$fulltextAvailable !== null) {
            return self::$fulltextAvailable;
        }

        try {
            $count = (int) Db::value(
                'SELECT COUNT(DISTINCT INDEX_NAME) FROM information_schema.STATISTICS
                  WHERE TABLE_SCHEMA = DATABASE()
                    AND INDEX_NAME IN ("ft_plants","ft_problems","ft_guides")'
            );
            return self::$fulltextAvailable = ($count === 3);
        } catch (PDOException) {
            return self::$fulltextAvailable = false;
        }
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
