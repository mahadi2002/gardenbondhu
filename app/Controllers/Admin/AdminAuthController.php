<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Crypto;
use App\Core\Db;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuditService;
use App\Support\Totp;

/**
 * Admin auth is deliberately separate from user auth: admins sign in with
 * email + Argon2id password, because they may not hold a Robi number at all.
 *
 * Admins who have enrolled TOTP (see AdminSecurityController) get a second
 * step: password success sets `admin_pending_id`, NOT `admin_id` — the
 * session is not an authenticated admin session until the code also checks
 * out. Admins without TOTP enrolled skip straight to `admin_id`, same as
 * before 2FA existed.
 */
final class AdminAuthController extends Controller
{
    public function form(Request $request): Response
    {
        if (Session::adminId() !== null) {
            return $this->redirect('/admin');
        }

        return $this->view('admin/login');
    }

    public function login(Request $request): Response
    {
        $validator = Validator::make($request->body, [
            'email'    => 'required|email|max:160',
            'password' => 'required|min:8|max:200',
        ], ['email' => 'Email', 'password' => 'Password']);

        if ($validator->fails()) {
            Session::notify('error', 'Email ও Password দিন।');
            return $this->redirect('/admin/login');
        }

        $email = strtolower((string) $validator->get('email'));
        $admin = Db::first(
            'SELECT id, email, name, role, password_hash, totp_secret, is_active FROM admins WHERE email = ?',
            [$email]
        );

        // Constant-ish work whether or not the account exists, so timing does
        // not reveal which admin emails are real.
        $hash  = $admin['password_hash'] ?? '$argon2id$v=19$m=65536,t=4,p=1$aaaaaaaaaaaaaaaa$aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
        $valid = password_verify((string) $validator->get('password'), (string) $hash);

        if (!$valid || $admin === null || (int) $admin['is_active'] !== 1) {
            AuditService::log('admin.login_failed', 'admin', null, 'admin', null, [
                'email' => $email,
            ], $request->ipHash());

            Session::notify('error', 'Email বা Password মিলছে না।');
            return $this->redirect('/admin/login');
        }

        if (password_needs_rehash((string) $admin['password_hash'], PASSWORD_ARGON2ID)) {
            Db::exec('UPDATE admins SET password_hash = ? WHERE id = ?', [
                password_hash((string) $validator->get('password'), PASSWORD_ARGON2ID),
                $admin['id'],
            ]);
        }

        Session::regenerate();
        Session::put('_ua', $request->uaHash());

        if ($admin['totp_secret'] !== null) {
            // Password checked out, but the session is not an admin session
            // yet — RequireAdmin only ever looks at admin_id.
            Session::put('admin_pending_id', (int) $admin['id']);
            // The password was correct — don't let a later TOTP retry (new
            // tab, session drop, typo) also count against admin_login and
            // lock out a legitimate admin who keeps entering the right
            // password. Full login only completes in verify(), but the
            // password-side limiter can reset here already.
            RateLimit::reset('admin_login', 'ip:' . $request->ipHash());
            AuditService::log('admin.login_password_ok', 'admin', (int) $admin['id'], 'admin', (int) $admin['id'], [], $request->ipHash());
            return $this->redirect('/admin/login/verify');
        }

        Session::put('admin_id', (int) $admin['id']);
        Db::exec('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
        RateLimit::reset('admin_login', 'ip:' . $request->ipHash());

        AuditService::log('admin.login', 'admin', (int) $admin['id'], 'admin', (int) $admin['id'], [], $request->ipHash());

        return $this->redirect('/admin');
    }

    public function verifyForm(Request $request): Response
    {
        if (Session::adminId() !== null) {
            return $this->redirect('/admin');
        }
        if (Session::get('admin_pending_id') === null) {
            return $this->redirect('/admin/login');
        }

        return $this->view('admin/login-verify');
    }

    public function verify(Request $request): Response
    {
        $pendingId = Session::get('admin_pending_id');
        if ($pendingId === null) {
            return $this->redirect('/admin/login');
        }
        $pendingId = (int) $pendingId;

        $validator = Validator::make($request->body, [
            'code' => 'required|digits:6',
        ], ['code' => 'Code']);

        $admin = Db::first(
            'SELECT id, email, totp_secret, is_active FROM admins WHERE id = ?',
            [$pendingId]
        );

        $secret = $admin !== null && $admin['totp_secret'] !== null
            ? Crypto::decrypt((string) $admin['totp_secret'])
            : null;

        $valid = !$validator->fails()
            && $admin !== null
            && (int) $admin['is_active'] === 1
            && $secret !== null
            && Totp::verify($secret, (string) $validator->get('code'));

        if (!$valid) {
            AuditService::log('admin.totp_failed', 'admin', $pendingId, 'admin', $pendingId, [], $request->ipHash());
            Session::notify('error', 'Code মিলছে না।');
            return $this->redirect('/admin/login/verify');
        }

        Session::forget('admin_pending_id');
        Session::regenerate();
        Session::put('admin_id', $pendingId);
        Session::put('_ua', $request->uaHash());

        Db::exec('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$pendingId]);
        RateLimit::reset('admin_login', 'ip:' . $request->ipHash());
        // Must match the RateLimiter middleware's admin_totp key format
        // (ip + pending-admin id) or this reset silently no-ops.
        RateLimit::reset('admin_totp', 'ip:' . $request->ipHash() . ':pending:' . $pendingId);

        AuditService::log('admin.login', 'admin', $pendingId, 'admin', $pendingId, ['totp' => true], $request->ipHash());

        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $adminId = Session::adminId();

        if ($adminId !== null) {
            AuditService::log('admin.logout', 'admin', $adminId, null, null, [], $request->ipHash());
        }

        Session::forget('admin_id');
        Session::forget('admin_pending_id');
        Session::regenerate();

        return $this->redirect('/admin/login');
    }
}
