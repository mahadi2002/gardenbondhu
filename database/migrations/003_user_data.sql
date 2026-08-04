-- 003_user_data.sql — personalisation, Q&A

CREATE TABLE IF NOT EXISTS user_plants (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id            BIGINT UNSIGNED NOT NULL,
  plant_id           INT UNSIGNED    DEFAULT NULL,
  nickname           VARCHAR(80)     DEFAULT NULL,
  planted_on         DATE            DEFAULT NULL,
  location           ENUM('balcony','rooftop','indoor','yard') NOT NULL DEFAULT 'balcony',
  pot_size_cm        SMALLINT UNSIGNED DEFAULT NULL,
  last_watered_at    DATE            DEFAULT NULL,
  last_fertilized_at DATE            DEFAULT NULL,
  notes              TEXT            DEFAULT NULL,
  photo              VARCHAR(200)    DEFAULT NULL,
  is_archived        TINYINT(1)      NOT NULL DEFAULT 0,
  created_at         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_up_user (user_id, is_archived),
  CONSTRAINT fk_up_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  CONSTRAINT fk_up_plant FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS care_tasks (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_plant_id BIGINT UNSIGNED NOT NULL,
  task          ENUM('water','fertilize','prune','repot','spray','check') NOT NULL,
  due_on        DATE            NOT NULL,
  done_at       DATETIME        DEFAULT NULL,
  auto_generated TINYINT(1)     NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_task_day (user_plant_id, task, due_on),
  KEY idx_task_due (due_on, done_at),
  CONSTRAINT fk_task_up FOREIGN KEY (user_plant_id) REFERENCES user_plants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS questions (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id     BIGINT UNSIGNED NOT NULL,
  plant_id    INT UNSIGNED    DEFAULT NULL,
  title       VARCHAR(200)    NOT NULL,
  body        TEXT            NOT NULL,
  image       VARCHAR(200)    DEFAULT NULL,
  status      ENUM('pending','approved','answered','rejected') NOT NULL DEFAULT 'pending',
  answer_count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_q_status (status, created_at),
  KEY idx_q_user (user_id),
  CONSTRAINT fk_q_user  FOREIGN KEY (user_id)  REFERENCES users(id)  ON DELETE CASCADE,
  CONSTRAINT fk_q_plant FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS answers (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_id  BIGINT UNSIGNED NOT NULL,
  user_id      BIGINT UNSIGNED DEFAULT NULL,
  admin_id     INT UNSIGNED    DEFAULT NULL,
  body         TEXT            NOT NULL,
  is_expert    TINYINT(1)      NOT NULL DEFAULT 0,
  is_accepted  TINYINT(1)      NOT NULL DEFAULT 0,
  upvotes      INT UNSIGNED    NOT NULL DEFAULT 0,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_a_question (question_id, is_expert, created_at),
  CONSTRAINT fk_a_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
  CONSTRAINT fk_a_user     FOREIGN KEY (user_id)     REFERENCES users(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS bookmarks (
  user_id     BIGINT UNSIGNED NOT NULL,
  entity_type ENUM('plant','problem','guide') NOT NULL,
  entity_id   INT UNSIGNED    NOT NULL,
  created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, entity_type, entity_id),
  CONSTRAINT fk_bm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
