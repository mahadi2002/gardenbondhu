-- 008_email_auth.sql — replace phone + carrier OTP auth with email + password
--
-- msisdn_hash/msisdn_enc/msisdn_last4/operator were only ever used by the
-- phone+OTP login flow and the carrier-billing reporting that went with it.
-- Both are gone (see 009_drop_billing.sql), so the columns go too. otp_requests
-- is dropped outright — nothing else in the app reads it (confirmed: only
-- OtpService, cron/cleanup.php, and docs referenced it, all removed/updated
-- in this pass).

ALTER TABLE users
  DROP INDEX uq_users_msisdn,
  DROP INDEX idx_users_operator,
  DROP COLUMN msisdn_hash,
  DROP COLUMN msisdn_enc,
  DROP COLUMN msisdn_last4,
  DROP COLUMN operator,
  ADD COLUMN email VARCHAR(191) NOT NULL AFTER id,
  ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER email,
  ADD UNIQUE KEY uq_users_email (email);

DROP TABLE IF EXISTS otp_requests;

-- Password reset tokens. Only the SHA-256 hash of the raw token is stored —
-- the raw token lives in the emailed link and nowhere else, same spirit as
-- the old otp_hash column (never store the secret itself).
CREATE TABLE IF NOT EXISTS password_resets (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id      BIGINT UNSIGNED NOT NULL,
  token_hash   CHAR(64)        NOT NULL,
  ip_hash      CHAR(64)        DEFAULT NULL,
  consumed_at  DATETIME        DEFAULT NULL,
  expires_at   DATETIME        NOT NULL,
  created_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pr_token (token_hash),
  KEY idx_pr_user (user_id, consumed_at, expires_at),
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
