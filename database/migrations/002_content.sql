-- 002_content.sql — the gardening knowledge base

CREATE TABLE IF NOT EXISTS plant_categories (
  id         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug       VARCHAR(60)  NOT NULL,
  name_bn    VARCHAR(80)  NOT NULL,
  name_en    VARCHAR(80)  DEFAULT NULL,
  icon       VARCHAR(40)  DEFAULT NULL,
  sort_order SMALLINT     NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seasons (
  id          TINYINT UNSIGNED NOT NULL,
  name_bn     VARCHAR(40) NOT NULL COMMENT 'গ্রীষ্ম, বর্ষা, শরৎ, হেমন্ত, শীত, বসন্ত',
  bn_months   VARCHAR(60) NOT NULL COMMENT 'বৈশাখ-জ্যৈষ্ঠ',
  month_start TINYINT UNSIGNED NOT NULL COMMENT 'Gregorian approx',
  month_end   TINYINT UNSIGNED NOT NULL,
  note_bn     VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plants (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug            VARCHAR(140) NOT NULL,
  name_bn         VARCHAR(120) NOT NULL,
  name_en         VARCHAR(120) DEFAULT NULL,
  scientific_name VARCHAR(160) DEFAULT NULL,
  local_names     JSON         DEFAULT NULL COMMENT 'আঞ্চলিক নামের array',
  category_id     SMALLINT UNSIGNED DEFAULT NULL,
  difficulty      ENUM('easy','medium','hard') NOT NULL DEFAULT 'easy',
  space_type      SET('balcony','rooftop','indoor','yard','pot') NOT NULL DEFAULT 'pot',
  sunlight        ENUM('full','partial','shade') NOT NULL DEFAULT 'partial',
  water_need      ENUM('low','medium','high')    NOT NULL DEFAULT 'medium',
  growth_habit    ENUM('herb','shrub','tree','climber','succulent','grass') DEFAULT 'shrub',
  soil_mix        TEXT         DEFAULT NULL,
  pot_size_cm     VARCHAR(30)  DEFAULT NULL,
  temp_min_c      TINYINT      DEFAULT NULL,
  temp_max_c      TINYINT      DEFAULT NULL,
  ph_min          DECIMAL(3,1) DEFAULT NULL,
  ph_max          DECIMAL(3,1) DEFAULT NULL,
  water_interval_days TINYINT UNSIGNED DEFAULT NULL COMMENT 'baseline for CareScheduler',
  fertilizer_interval_days SMALLINT UNSIGNED DEFAULT NULL,
  propagation     TEXT         DEFAULT NULL,
  fertilizer_note TEXT         DEFAULT NULL,
  pruning_note    TEXT         DEFAULT NULL,
  harvest_days    SMALLINT UNSIGNED DEFAULT NULL,
  toxic_to_pets   TINYINT(1)   NOT NULL DEFAULT 0,
  summary_bn      TEXT         DEFAULT NULL COMMENT 'FREE preview',
  body_bn         MEDIUMTEXT   DEFAULT NULL COMMENT 'PAID — markdown',
  hero_image      VARCHAR(200) DEFAULT NULL,
  gallery         JSON         DEFAULT NULL,
  view_count      INT UNSIGNED NOT NULL DEFAULT 0,
  is_published    TINYINT(1)   NOT NULL DEFAULT 0,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plant_slug (slug),
  KEY idx_plant_cat (category_id, is_published),
  KEY idx_plant_filter (sunlight, water_need, difficulty),
  CONSTRAINT fk_plant_cat FOREIGN KEY (category_id) REFERENCES plant_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plant_seasons (
  plant_id  INT UNSIGNED     NOT NULL,
  season_id TINYINT UNSIGNED NOT NULL,
  activity  ENUM('sow','plant','fertilize','prune','harvest','repot','protect') NOT NULL,
  note_bn   VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (plant_id, season_id, activity),
  KEY idx_ps_season (season_id, activity),
  CONSTRAINT fk_ps_plant  FOREIGN KEY (plant_id)  REFERENCES plants(id)  ON DELETE CASCADE,
  CONSTRAINT fk_ps_season FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS symptoms (
  id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug      VARCHAR(80)  NOT NULL,
  name_bn   VARCHAR(160) NOT NULL COMMENT 'পাতা হলুদ হয়ে যাচ্ছে',
  body_part ENUM('leaf','stem','root','flower','fruit','soil','whole') NOT NULL,
  icon      VARCHAR(40)  DEFAULT NULL,
  sort_order SMALLINT    NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_symptom_slug (slug),
  KEY idx_symptom_part (body_part, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS problems (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug               VARCHAR(140) NOT NULL,
  name_bn            VARCHAR(160) NOT NULL,
  name_en            VARCHAR(160) DEFAULT NULL,
  type               ENUM('pest','fungal','bacterial','viral','nutrient','cultural','environmental') NOT NULL,
  severity           ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  description_bn     TEXT         DEFAULT NULL COMMENT 'FREE preview',
  identification_bn  TEXT         DEFAULT NULL,
  organic_remedy_bn  TEXT         DEFAULT NULL COMMENT 'PAID — shown first',
  chemical_remedy_bn TEXT         DEFAULT NULL COMMENT 'PAID — with dosage + safety',
  prevention_bn      TEXT         DEFAULT NULL,
  safety_note_bn     VARCHAR(500) DEFAULT NULL,
  images             JSON         DEFAULT NULL,
  is_published       TINYINT(1)   NOT NULL DEFAULT 0,
  created_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_problem_slug (slug),
  KEY idx_problem_type (type, severity, is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS problem_symptoms (
  problem_id INT UNSIGNED     NOT NULL,
  symptom_id INT UNSIGNED     NOT NULL,
  weight     TINYINT UNSIGNED NOT NULL DEFAULT 5 COMMENT '1..10, drives diagnosis score',
  PRIMARY KEY (problem_id, symptom_id),
  KEY idx_psym_symptom (symptom_id),
  CONSTRAINT fk_psym_problem FOREIGN KEY (problem_id) REFERENCES problems(id) ON DELETE CASCADE,
  CONSTRAINT fk_psym_symptom FOREIGN KEY (symptom_id) REFERENCES symptoms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plant_problems (
  plant_id   INT UNSIGNED NOT NULL,
  problem_id INT UNSIGNED NOT NULL,
  frequency  ENUM('common','occasional','rare') NOT NULL DEFAULT 'common',
  PRIMARY KEY (plant_id, problem_id),
  KEY idx_pp_problem (problem_id),
  CONSTRAINT fk_pp_plant   FOREIGN KEY (plant_id)   REFERENCES plants(id)   ON DELETE CASCADE,
  CONSTRAINT fk_pp_problem FOREIGN KEY (problem_id) REFERENCES problems(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS guides (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug         VARCHAR(140) NOT NULL,
  title_bn     VARCHAR(200) NOT NULL,
  category     ENUM('basic','soil','water','fertilizer','pest','propagation','tools','rooftop','indoor','season') NOT NULL DEFAULT 'basic',
  excerpt_bn   TEXT         DEFAULT NULL COMMENT 'FREE',
  body_bn      MEDIUMTEXT   DEFAULT NULL COMMENT 'PAID — markdown',
  cover_image  VARCHAR(200) DEFAULT NULL,
  read_minutes TINYINT UNSIGNED DEFAULT NULL,
  is_premium   TINYINT(1)   NOT NULL DEFAULT 1,
  is_published TINYINT(1)   NOT NULL DEFAULT 0,
  published_at DATETIME     DEFAULT NULL,
  view_count   INT UNSIGNED NOT NULL DEFAULT 0,
  updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_guide_slug (slug),
  KEY idx_guide_cat (category, is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
