<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ContactRepo;
use App\Repositories\GuideRepo;
use App\Repositories\PlantRepo;
use App\Repositories\ProblemRepo;
use App\Repositories\QuestionRepo;
use App\Repositories\UserRepo;

final class AdminDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $users = new UserRepo();

        return $this->view('admin/dashboard', [
            'activeUsers'      => $users->countByStatus('active'),
            'newUsers7d'       => $users->countNewSince(7),
            'pendingQuestions' => (new QuestionRepo())->pendingCount(),
            'newContacts'      => (new ContactRepo())->newCount(),
            'content'          => [
                'plants'   => (new PlantRepo())->publishedCount(),
                'problems' => (new ProblemRepo())->publishedCount(),
                'guides'   => (new GuideRepo())->publishedCount(),
            ],
        ]);
    }

    public function logs(Request $request): Response
    {
        $action = $request->str('action');
        $actor  = $request->str('actor');

        $where  = ['1 = 1'];
        $params = [];

        if ($action !== '') {
            $where[]  = 'action LIKE ?';
            $params[] = $action . '%';
        }
        if (in_array($actor, ['user', 'admin', 'system', 'gateway'], true)) {
            $where[]  = 'actor_type = ?';
            $params[] = $actor;
        }

        $rows = Db::all(
            'SELECT id, actor_type, actor_id, action, entity, entity_id, meta, created_at
               FROM audit_log
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY id DESC
              LIMIT 200',
            $params
        );

        $actions = Db::all('SELECT DISTINCT action FROM audit_log ORDER BY action LIMIT 100');

        return $this->view('admin/logs', [
            'rows'    => $rows,
            'actions' => $actions,
            'action'  => $action,
            'actor'   => $actor,
        ]);
    }
}
