<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Db;
use App\Repositories\UserPlantRepo;
use App\Services\CareScheduler;

final class CalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $month = $request->int('month', (int) date('n'));
        $month = ($month >= 1 && $month <= 12) ? $month : (int) date('n');

        $seasonId = CareScheduler::currentSeasonId($month);
        $season   = Db::first('SELECT * FROM seasons WHERE id = ?', [$seasonId]);

        $activities = CareScheduler::monthlyActivities($month);
        $byActivity = [];
        foreach ($activities as $row) {
            $byActivity[(string) $row['activity']][] = $row;
        }

        $userId = (int) $this->currentUserId();

        return $this->view('app/calendar', [
            'month'       => $month,
            'monthName'   => DashboardController::monthNameBn($month),
            'season'      => $season,
            'byActivity'  => $byActivity,
            'labels'      => (array) config('content.activity'),
            'myTasks'     => (new UserPlantRepo())->tasksForUser(
                $userId,
                date('Y-m-01', mktime(0, 0, 0, $month, 1)),
                date('Y-m-t', mktime(0, 0, 0, $month, 1))
            ),
        ]);
    }
}
