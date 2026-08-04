<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\SubscriptionService;

/**
 * Users are searched and displayed by msisdn_last4 ONLY. A full number is
 * never rendered in the admin UI — it is not even selected (spec §11).
 */
final class AdminUserController extends Controller
{
    public function index(Request $request): Response
    {
        $repo  = new UserRepo();
        $last4 = preg_replace('/\D+/', '', $request->str('last4')) ?? '';

        return $this->view('admin/users/index', [
            'users' => strlen($last4) === 4 ? $repo->searchByLast4($last4) : $repo->recent(),
            'last4' => $last4,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $user = (new UserRepo())->find((int) $id);
        if ($user === null) {
            $this->notFound();
        }

        $subs = Db::all('SELECT * FROM subscriptions WHERE user_id = ? ORDER BY id DESC', [(int) $id]);
        $repo = new SubscriptionRepo();

        $charges = [];
        foreach ($subs as $sub) {
            $charges[(int) $sub['id']] = $repo->chargesForSubscription((int) $sub['id'], 20);
        }

        return $this->view('admin/users/show', [
            'user'      => $user,
            'subs'      => $subs,
            'charges'   => $charges,
            'hasAccess' => SubscriptionService::hasAccess((int) $id),
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

            case 'extend_grace':
                $hours = max(1, min(168, (int) $request->int('hours', 48)));
                $sub   = (new SubscriptionRepo())->latestForUser($userId);

                if ($sub === null) {
                    Session::notify('error', 'কোনো Subscription পাওয়া যায়নি।');
                    break;
                }

                Db::exec(
                    'UPDATE subscriptions
                        SET status = "grace",
                            grace_until = DATE_ADD(NOW(), INTERVAL ? HOUR),
                            current_period_end = DATE_ADD(NOW(), INTERVAL ? HOUR)
                      WHERE id = ?',
                    [$hours, $hours, $sub['id']]
                );

                Session::notify('success', bn_num($hours) . ' ঘণ্টার Grace দেওয়া হয়েছে।');
                break;

            default:
                Session::notify('error', 'অজানা কাজ।');
                return $this->redirect('/admin/users/' . $userId);
        }

        AuditService::log('admin.user.' . $action, 'admin', $adminId, 'user', $userId, [], $request->ipHash());

        return $this->redirect('/admin/users/' . $userId);
    }
}
