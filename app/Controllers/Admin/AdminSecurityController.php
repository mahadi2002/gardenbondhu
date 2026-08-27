<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Crypto;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuditService;
use App\Support\Totp;

/**
 * Admin self-service TOTP enrollment. Separate from AdminAuthController,
 * which only handles the login-time verification step once a secret exists.
 *
 * Enrollment is two steps on purpose: generate a secret and show it (`setup`),
 * then require one live code back (`confirm`) before it is ever written to
 * `admins.totp_secret` — a secret nobody has proven they can actually
 * generate codes from is worse than no 2FA, since it just locks the admin
 * out at next login.
 */
final class AdminSecurityController extends Controller
{
    public function index(Request $request): Response
    {
        $admin = Db::first('SELECT id, totp_secret FROM admins WHERE id = ?', [Session::adminId()]);

        return $this->view('admin/security/index', [
            'totpEnabled' => $admin !== null && $admin['totp_secret'] !== null,
        ]);
    }

    public function setup(Request $request): Response
    {
        $admin = Db::first('SELECT id, email, totp_secret FROM admins WHERE id = ?', [Session::adminId()]);

        if ($admin !== null && $admin['totp_secret'] !== null) {
            Session::notify('info', '2FA আগে থেকেই চালু আছে। নতুন করে বসাতে চাইলে আগে বন্ধ করুন।');
            return $this->redirect('/admin/security');
        }

        // A fresh secret every time this page loads — only ever written to
        // the DB once a live code proves it round-trips (see confirm()).
        $secret = Totp::generateSecret();
        Session::put('admin_totp_pending_secret', $secret);

        return $this->view('admin/security/totp-setup', [
            'secret'  => $secret,
            'display' => Totp::formatForDisplay($secret),
            'uri'     => Totp::provisioningUri($secret, (string) ($admin['email'] ?? 'admin'), (string) config('app.name')),
        ]);
    }

    public function confirm(Request $request): Response
    {
        $adminId = Session::adminId();
        $secret  = Session::get('admin_totp_pending_secret');

        if (!is_string($secret) || $secret === '') {
            Session::notify('error', 'Setup Session-এর মেয়াদ শেষ। আবার শুরু করুন।');
            return $this->redirect('/admin/security/totp/setup');
        }

        $validator = Validator::make($request->body, [
            'code' => 'required|digits:6',
        ], ['code' => 'Code']);

        if ($validator->fails() || !Totp::verify($secret, (string) $validator->get('code'))) {
            Session::notify('error', 'Code মিলছে না। Authenticator app থেকে বর্তমান Code আবার দিন।');
            return $this->redirect('/admin/security/totp/setup');
        }

        Db::exec('UPDATE admins SET totp_secret = ? WHERE id = ?', [Crypto::encrypt($secret), $adminId]);
        Session::forget('admin_totp_pending_secret');

        AuditService::log('admin.totp_enrolled', 'admin', $adminId, 'admin', $adminId, [], $request->ipHash());
        Session::notify('success', '2FA চালু হয়েছে। পরের বার Login-এ Code লাগবে।');

        return $this->redirect('/admin/security');
    }

    public function disable(Request $request): Response
    {
        $adminId = Session::adminId();
        $admin   = Db::first('SELECT id, password_hash FROM admins WHERE id = ?', [$adminId]);

        $validator = Validator::make($request->body, [
            'password' => 'required|min:8|max:200',
        ], ['password' => 'Password']);

        $valid = !$validator->fails()
            && $admin !== null
            && password_verify((string) $validator->get('password'), (string) $admin['password_hash']);

        if (!$valid) {
            Session::notify('error', 'Password মিলছে না — 2FA বন্ধ হয়নি।');
            return $this->redirect('/admin/security');
        }

        Db::exec('UPDATE admins SET totp_secret = NULL WHERE id = ?', [$adminId]);
        Session::forget('admin_totp_pending_secret');

        AuditService::log('admin.totp_disabled', 'admin', $adminId, 'admin', $adminId, [], $request->ipHash());
        Session::notify('success', '2FA বন্ধ করা হয়েছে।');

        return $this->redirect('/admin/security');
    }
}
