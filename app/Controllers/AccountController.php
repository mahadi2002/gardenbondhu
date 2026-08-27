<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\UserPlantRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;

final class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $this->currentUserId();

        return $this->view('account/index', [
            'user'       => (new UserRepo())->find($userId),
            'plantCount' => (new UserPlantRepo())->countForUser($userId),
        ]);
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

        // The row survives so foreign keys stay intact; every identifier on
        // it does not.
        (new UserRepo())->anonymize($userId);

        AuditService::log('account.deleted', 'user', $userId, 'user', $userId, [], $request->ipHash());

        Session::revokeAllForUser($userId);
        Session::destroy_all();
        Session::start($request);
        Session::notify('success', 'আপনার অ্যাকাউন্ট মুছে ফেলা হয়েছে।');

        return $this->redirect('/');
    }
}
