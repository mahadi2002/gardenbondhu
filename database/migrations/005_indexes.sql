-- 005_indexes.sql — FULLTEXT (run last; the ngram parser is needed for Bangla)
--
--   MySQL 8: ngram works.
--   MariaDB: the ngram parser is NOT available — migrate.php tolerates the
--   failure of this file and SearchService falls back to LIKE '%term%', which
--   is fine at this content volume (60 plants / 40 problems / 20 guides).
--
--   For best results set ngram_token_size=2 in my.cnf (requires a restart).

ALTER TABLE plants   ADD FULLTEXT KEY ft_plants   (name_bn, name_en, summary_bn) WITH PARSER ngram;
ALTER TABLE problems ADD FULLTEXT KEY ft_problems (name_bn, name_en, description_bn) WITH PARSER ngram;
ALTER TABLE guides   ADD FULLTEXT KEY ft_guides   (title_bn, excerpt_bn) WITH PARSER ngram;
