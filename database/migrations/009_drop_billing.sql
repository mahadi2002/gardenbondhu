-- 009_drop_billing.sql — remove carrier-billing/subscription infrastructure
--
-- Phase 1 rebrand: this is a hobby project now — login-or-registered access
-- only, no subscription/billing. charge_transactions references
-- subscriptions via a foreign key, so it has to go first.

DROP TABLE IF EXISTS charge_transactions;
DROP TABLE IF EXISTS webhook_events;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS subscriptions;
