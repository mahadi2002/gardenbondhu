<?php
declare(strict_types=1);

/**
 * Daily 03:00 — retention purge.
 *
 * Cron entry:
 *   0 3 * * *  /usr/local/bin/php /home/USER/gardenbondhu/cron/cleanup.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Db;
use App\Core\Logger;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('cleanup');
if ($release === null) {
    exit(0);
}

$retention = (array) config('app.retention');
$report    = [];

$report['rate_limits'] = Db::exec(
    'DELETE FROM rate_limits WHERE window_start < DATE_SUB(NOW(), INTERVAL ? DAY)
       AND (blocked_until IS NULL OR blocked_until < NOW())',
    [(int) $retention['ratelimit_days']]
);

$report['sessions'] = Db::exec(
    'DELETE FROM sessions WHERE last_activity < ?',
    [time() - ((int) $retention['session_days'] * 86400)]
);

$report['audit_log'] = Db::exec(
    'DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)',
    [(int) $retention['audit_days']]
);

$report['password_resets'] = Db::exec(
    'DELETE FROM password_resets WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)'
);

Logger::info('cleanup.done', $report);
fwrite(STDOUT, "cleanup: " . json_encode($report) . "\n");

$release();
