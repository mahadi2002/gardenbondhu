<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\SubscriptionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\SubscriptionService;

/**
 * Account routes require auth but NOT an active subscription — an expired user
 * must be able to reach the page that explains how to fix their billing.
 */
final class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $this->currentUserId();

        $user = (new UserRepo())->find($userId);
        $sub  = SubscriptionService::current($userId);

        return $this->view('account/index', [
            'user'    => $user,
            'sub'     => $sub,
            'charges' => $sub === null ? [] : (new SubscriptionRepo())->chargesForSubscription((int) $sub['id'], 10),
            'hasAccess' => SubscriptionService::hasAccess($userId),
        ]);
    }

    public function expired(Request $request): Response
    {
        $userId = (int) $this->currentUserId();

        // Someone who regained access should not be stranded on this page.
        if (SubscriptionService::hasAccess($userId)) {
            return $this->redirect('/app');
        }

        return $this->view('errors/expired', [
            'sub' => SubscriptionService::current($userId),
        ], 200);
    }

    public function unsubscribeForm(Request $request): Response
    {
        return $this->view('account/unsubscribe', [
            'sub' => SubscriptionService::current((int) $this->currentUserId()),
        ]);
    }

    public function unsubscribe(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $sub    = SubscriptionService::current($userId);

        if ($sub === null || $sub['status'] === 'unsubscribed') {
            Session::notify('info', 'আপনার Subscription আগে থেকেই বন্ধ আছে।');
            return $this->redirect('/account');
        }

        $reason = substr($request->str('reason'), 0, 160);

        (new SubscriptionService())->unsubscribe((int) $sub['id'], 'user', $reason);

        AuditService::log('account.unsubscribed', 'user', $userId, 'subscription', (int) $sub['id'], [
            'reason' => $reason,
        ], $request->ipHash());

        // Access is gone, so the session went with it. Start a clean one and
        // tell them what happened rather than dropping them on a login screen.
        Session::destroy_all();
        Session::start($request);
        Session::notify('success', 'আপনার Subscription বন্ধ হয়েছে। আজকের পর থেকে আর টাকা কাটা হবে না।');

        return $this->redirect('/');
    }

    public function destroy(Request $request): Response
    {
        $userId = (int) $this->currentUserId();

        $validator = Validator::make($request->body, [
            'confirm' => 'required|in:DELETE',
        ], ['confirm' => 'নিশ্চিতকরণ']);

        if ($validator->fails()) {
            Session::notify('error', 'অ্যাকাউন্ট মুছতে ঘরে DELETE লিখুন।');
            return $this->redirect('/account');
        }

        $sub = SubscriptionService::current($userId);
        if ($sub !== null && $sub['status'] !== 'unsubscribed') {
            (new SubscriptionService())->unsubscribe((int) $sub['id'], 'user', 'account deleted');
        }

        // The row survives so billing history and foreign keys stay intact;
        // every identifier on it does not.
        (new UserRepo())->anonymize($userId);

        AuditService::log('account.deleted', 'user', $userId, 'user', $userId, [], $request->ipHash());

        Session::revokeAllForUser($userId);
        Session::destroy_all();
        Session::start($request);
        Session::notify('success', 'আপনার অ্যাকাউন্ট মুছে ফেলা হয়েছে।');

        return $this->redirect('/');
    }
}
