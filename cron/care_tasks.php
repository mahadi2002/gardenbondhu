<?php
declare(strict_types=1);

/**
 * Daily 05:00 — generate/roll care_tasks for every user_plants row (spec §8.5).
 *
 * Cron entry:
 *   0 5 * * *  /usr/local/bin/php /home/USER/gardenbondhu/cron/care_tasks.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';
require __DIR__ . '/_lock.php';

use App\Core\Logger;
use App\Services\CareScheduler;

if (PHP_SAPI !== 'cli') {
    exit(1);
}

$release = cron_lock('care_tasks');
if ($release === null) {
    exit(0);
}

$created = (new CareScheduler())->generateForAll();

Logger::info('care_tasks.done', ['created' => $created]);
fwrite(STDOUT, "care_tasks: created $created task(s).\n");

$release();
