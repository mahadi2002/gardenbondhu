<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Services\AuditService;

/**
 * Admin auth is deliberately separate from user auth: admins sign in with
 * email + Argon2id password, because they may not hold a Robi number at all.
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
            'SELECT id, email, name, role, password_hash, is_active FROM admins WHERE email = ?',
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
        Session::put('admin_id', (int) $admin['id']);
        Session::put('_ua', $request->uaHash());

        Db::exec('UPDATE admins SET last_login_at = NOW() WHERE id = ?', [$admin['id']]);
        RateLimit::reset('admin_login', 'ip:' . $request->ipHash());

        AuditService::log('admin.login', 'admin', (int) $admin['id'], 'admin', (int) $admin['id'], [], $request->ipHash());

        return $this->redirect('/admin');
    }

    public function logout(Request $request): Response
    {
        $adminId = Session::adminId();

        if ($adminId !== null) {
            AuditService::log('admin.logout', 'admin', $adminId, null, null, [], $request->ipHash());
        }

        Session::forget('admin_id');
        Session::regenerate();

        return $this->redirect('/admin/login');
    }
}
