-- 007_drop_bookmarks.sql — remove the dead `bookmarks` table
--
-- Never wired up: no controller, repository, or view reads or writes it.
-- Bookmarking never shipped as a feature, so there is nothing to migrate off
-- of — just dropping the unused table.

DROP TABLE IF EXISTS bookmarks;
