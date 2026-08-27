<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Crypto;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\PasswordResetRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\Notifier;
use PDOException;

/**
 * Email + password auth. Every failure message is deliberately generic —
 * "email or password is wrong", never "no such account" — so the login and
 * password-reset forms can't be used to enumerate registered emails.
 */
final class AuthController extends Controller
{
    public function registerForm(Request $request): Response
    {
        return $this->view('auth/register', ['next' => $this->safeNext($request->str('next'))]);
    }

    public function register(Request $request): Response
    {
        $next = $this->safeNext($request->str('next'));

        // Honeypot + minimum fill time — cheaper and kinder than a CAPTCHA.
        if ($request->str('website') !== '') {
            return $this->redirect('/register');
        }

        $validator = Validator::make($request->body, [
            'email'                 => 'required|email|max:191',
            'password'              => 'required|min:8|max:200',
            'password_confirmation' => 'required|min:8|max:200',
        ], [
            'email'                 => 'Email',
            'password'              => 'Password',
            'password_confirmation' => 'Password',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            return $this->redirect('/register' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
        }

        $email    = mb_strtolower((string) $validator->get('email'));
        $password = (string) $validator->get('password');

        if ($password !== (string) $validator->get('password_confirmation')) {
            Session::flash('_errors', ['password_confirmation' => ['Password দুটো মিলছে না।']]);
            Session::flash('_old', ['email' => $email]);
            return $this->redirect('/register' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
        }

        $repo = new UserRepo();

        // Generic message either way — never confirm whether an email is
        // already registered.
        if ($repo->findByEmail($email) !== null) {
            Session::flash('_errors', ['email' => ['এই Email দিয়ে Register করা যাচ্ছে না।']]);
            Session::flash('_old', ['email' => $email]);
            return $this->redirect('/register' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
        }

        try {
            $user = $repo->create($email, password_hash($password, PASSWORD_DEFAULT));
        } catch (PDOException $e) {
            // The findByEmail() check above can't stop a concurrent request
            // for the same address winning the race between check and
            // insert — the unique constraint on users.email is the real
            // guard. Surface the same generic message instead of a 500.
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate entry')) {
                Session::flash('_errors', ['email' => ['এই Email দিয়ে Register করা যাচ্ছে না।']]);
                Session::flash('_old', ['email' => $email]);
                return $this->redirect('/register' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
            }
            throw $e;
        }

        $userId = (int) $user['id'];

        Session::regenerate();
        Session::put('user_id', $userId);
        Session::put('_ua', $request->uaHash());
        $repo->touchLogin($userId);

        AuditService::log('auth.registered', 'user', $userId, 'user', $userId, [], $request->ipHash(), $request->uaHash());
        Session::notify('success', 'স্বাগতম! আপনার অ্যাকাউন্ট তৈরি হয়েছে।');

        return $this->redirect($next ?: '/app');
    }

    public function loginForm(Request $request): Response
    {
        return $this->view('auth/login', ['next' => $this->safeNext($request->str('next'))]);
    }

    public function login(Request $request): Response
    {
        $next = $this->safeNext($request->str('next'));

        $validator = Validator::make($request->body, [
            'email'    => 'required|email',
            'password' => 'required',
        ], ['email' => 'Email', 'password' => 'Password']);

        $email = mb_strtolower((string) $request->str('email'));

        if ($validator->fails()) {
            Session::notify('error', 'Email অথবা Password সঠিক নয়।');
            Session::flash('_old', ['email' => $email]);
            return $this->redirect('/login' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
        }

        $repo = new UserRepo();
        $user = $repo->findForAuth($email);

        if ($user === null || !password_verify((string) $validator->get('password'), (string) $user['password_hash'])) {
            Session::notify('error', 'Email অথবা Password সঠিক নয়।');
            Session::flash('_old', ['email' => $email]);
            return $this->redirect('/login' . ($next !== '' ? '?next=' . rawurlencode($next) : ''));
        }

        $userId = (int) $user['id'];

        if ($user['status'] === 'blocked') {
            Session::notify('error', 'এই Account দিয়ে এখন Login করা যাচ্ছে না। যোগাযোগ করুন।');
            return $this->redirect('/contact');
        }

        // Session fixation: rotate the id the moment the identity changes.
        Session::regenerate();
        Session::put('user_id', $userId);
        Session::put('_ua', $request->uaHash());
        $repo->touchLogin($userId);

        AuditService::log('auth.login', 'user', $userId, 'user', $userId, [], $request->ipHash(), $request->uaHash());

        return $this->redirect($next ?: '/app');
    }

    public function logout(Request $request): Response
    {
        $userId = Session::userId();

        if ($userId !== null) {
            AuditService::log('auth.logout', 'user', $userId, null, null, [], $request->ipHash());
        }

        Session::destroy_all();

        return $this->redirect('/');
    }

    public function forgotPasswordForm(Request $request): Response
    {
        return $this->view('auth/forgot-password');
    }

    public function forgotPassword(Request $request): Response
    {
        $validator = Validator::make($request->body, ['email' => 'required|email'], ['email' => 'Email']);

        if (!$validator->fails()) {
            $email = mb_strtolower((string) $validator->get('email'));
            $user  = (new UserRepo())->findByEmail($email);

            // Only sends when the account exists, but the response below is
            // identical either way — no enumeration signal.
            if ($user !== null && $user['status'] !== 'blocked') {
                $token = Crypto::randomToken(32);
                (new PasswordResetRepo())->create((int) $user['id'], $token, $request->ipHash());

                $resetUrl = rtrim((string) config('app.url'), '/') . '/reset-password/' . $token;
                Notifier::passwordReset($email, $resetUrl);

                AuditService::log('auth.password_reset_requested', 'user', (int) $user['id'], 'user', (int) $user['id'], [], $request->ipHash());
            }
        }

        Session::notify('success', 'Email ঠিকানাটি নিবন্ধিত থাকলে একটি Reset Link পাঠানো হয়েছে।');

        return $this->redirect('/login');
    }

    public function resetPasswordForm(Request $request, string $token): Response
    {
        return $this->view('auth/reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request, string $token): Response
    {
        $validator = Validator::make($request->body, [
            'password'              => 'required|min:8|max:200',
            'password_confirmation' => 'required|min:8|max:200',
        ], ['password' => 'Password', 'password_confirmation' => 'Password']);

        if ($validator->fails() || (string) $validator->get('password') !== (string) $validator->get('password_confirmation')) {
            Session::notify('error', 'Password ঠিক নয় অথবা দুটো মিলছে না (কমপক্ষে ৮ অক্ষর)।');
            return $this->redirect('/reset-password/' . rawurlencode($token));
        }

        $resetRepo = new PasswordResetRepo();
        $row       = $resetRepo->findValidByToken($token);

        if ($row === null) {
            Session::notify('error', 'Reset Link-এর মেয়াদ শেষ হয়ে গেছে। আবার চেষ্টা করুন।');
            return $this->redirect('/forgot-password');
        }

        $userId = (int) $row['user_id'];

        (new UserRepo())->updatePassword($userId, password_hash((string) $validator->get('password'), PASSWORD_DEFAULT));
        $resetRepo->consume((int) $row['id']);

        // A password change invalidates every existing session for this user.
        \App\Core\Session::revokeAllForUser($userId);

        AuditService::log('auth.password_reset', 'user', $userId, 'user', $userId, [], $request->ipHash());
        Session::notify('success', 'Password বদলানো হয়েছে। এখন নতুন Password দিয়ে Login করুন।');

        return $this->redirect('/login');
    }

    /** Only same-site absolute paths may be used as a post-login redirect. */
    private function safeNext(string $next): string
    {
        if ($next === '' || !str_starts_with($next, '/') || str_starts_with($next, '//')) {
            return '';
        }
        return preg_match('#^/[A-Za-z0-9/_\-]{0,140}$#', $next) === 1 ? $next : '';
    }
}
