<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepo;
use App\Services\AuditService;

/**
 * Users are searched and displayed by email. Every registered user has full
 * access — there is no subscription/grace concept left to report on here.
 */
final class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $repo = new UserRepo();
        $q    = trim($request->str('q'));

        return $this->view('admin/users/index', [
            'users' => $q !== '' ? $repo->searchByEmail($q) : $repo->recent(),
            'q'     => $q,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $user = (new UserRepo())->find((int) $id);
        if ($user === null) {
            $this->notFound();
        }

        return $this->view('admin/users/show', [
            'user'      => $user,
            'plantCount' => (int) Db::value('SELECT COUNT(*) FROM user_plants WHERE user_id = ?', [(int) $id]),
            'questionCount' => (int) Db::value('SELECT COUNT(*) FROM questions WHERE user_id = ?', [(int) $id]),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $userId = (int) $id;
        $repo   = new UserRepo();

        if ($repo->find($userId) === null) {
            $this->notFound();
        }

        $action  = $request->str('action');
        $adminId = Session::adminId();

        switch ($action) {
            case 'block':
                $repo->setStatus($userId, 'blocked');
                Session::revokeAllForUser($userId);
                Session::notify('success', 'ব্যবহারকারী Block করা হয়েছে।');
                break;

            case 'unblock':
                $repo->setStatus($userId, 'active');
                Session::notify('success', 'Block তুলে নেওয়া হয়েছে।');
                break;

            case 'mark_expert':
                $repo->setRole($userId, 'expert');
                Session::notify('success', 'বিশেষজ্ঞ হিসেবে চিহ্নিত হয়েছে।');
                break;

            case 'mark_user':
                $repo->setRole($userId, 'user');
                Session::notify('success', 'সাধারণ ব্যবহারকারী হিসেবে চিহ্নিত হয়েছে।');
                break;

            default:
                Session::notify('error', 'অজানা কাজ।');
                return $this->redirect('/admin/users/' . $userId);
        }

        AuditService::log('admin.user.' . $action, 'admin', $adminId, 'user', $userId, [], $request->ipHash());

        return $this->redirect('/admin/users/' . $userId);
    }
}
