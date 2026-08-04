<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\GuideRepo;
use App\Repositories\PlantRepo;
use App\Repositories\QuestionRepo;
use App\Repositories\UserPlantRepo;
use App\Services\CareScheduler;

final class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId    = (int) $this->currentUserId();
        $userPlant = new UserPlantRepo();

        $today = date('Y-m-d');
        $week  = date('Y-m-d', strtotime('+7 days'));

        $tasks = $userPlant->tasksForUser($userId, date('Y-m-d', strtotime('-30 days')), $week);

        $todayTasks = array_values(array_filter(
            $tasks,
            static fn(array $t): bool => $t['done_at'] === null && $t['due_on'] <= $today
        ));

        $weekTasks = array_values(array_filter(
            $tasks,
            static fn(array $t): bool => $t['done_at'] === null && $t['due_on'] > $today
        ));

        return $this->view('app/dashboard', [
            'todayTasks'  => $todayTasks,
            'weekTasks'   => array_slice($weekTasks, 0, 12),
            'plantCount'  => $userPlant->countForUser($userId),
            'myPlants'    => array_slice($userPlant->forUser($userId), 0, 4),
            'picks'       => (new PlantRepo())->beginnerPicks(4),
            'guides'      => (new GuideRepo())->latest(3),
            'questions'   => array_slice((new QuestionRepo())->feed(4), 0, 4),
            'season'      => CareScheduler::currentSeasonId(),
            'monthName'   => self::monthNameBn((int) date('n')),
        ]);
    }

    public static function monthNameBn(int $month): string
    {
        $names = [
            1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
            'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর',
        ];
        return $names[$month] ?? '';
    }
}
