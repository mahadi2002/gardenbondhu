<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Crypto;
use App\Core\RateLimit;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Exceptions\HttpException;

/** Generic IP/user-keyed limiter used as `rl:<bucket>` in the route table. */
final class RateLimiter implements Middleware
{
    public function __construct(private string $bucket)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = match ($this->bucket) {
            'qa_post' => 'user:' . (Session::userId() ?? 0),
            // IP + email together — one shared IP failing a dozen different
            // accounts' passwords shouldn't lock out the whole office, and
            // one attacker rotating email guesses shouldn't dodge the limit.
            'login', 'register' =>
                'ip:' . $request->ipHash() . ':email:' . mb_strtolower(trim($request->str('email'))),
            // The /forgot-password request step has an email field, but the
            // /reset-password/{token} completion step does not — keying that
            // step on email degrades to IP-only and locks out everyone on a
            // shared/NAT connection after 3 unrelated resets. The token in
            // the URL uniquely identifies the individual attempt, so key on
            // that instead whenever we're on the completion route.
            'password_reset' => str_starts_with($request->path, '/reset-password/')
                ? 'ip:' . $request->ipHash() . ':token:' . Crypto::blindIndex(substr($request->path, strlen('/reset-password/')))
                : 'ip:' . $request->ipHash() . ':email:' . mb_strtolower(trim($request->str('email'))),
            // Bound to the admin currently mid-login (admin_pending_id) or,
            // for the enrollment-confirm step, the already-authenticated
            // admin — an attacker rotating IPs is still slowed per-admin,
            // not just per-source-IP.
            'admin_totp' => 'ip:' . $request->ipHash() . ':pending:' . ((int) (Session::get('admin_pending_id') ?? Session::adminId() ?? 0)),
            default   => 'ip:' . $request->ipHash(),
        };

        $retry = RateLimit::hit($this->bucket, $key);

        if ($retry !== null) {
            throw new HttpException(429, 'অনেকবার চেষ্টা হয়েছে। ' . RateLimit::humanWait($retry) . ' পর আবার চেষ্টা করুন।', [
                'retry_after' => $retry,
            ]);
        }

        return $next();
    }
}
