<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\SubscriptionService;

/**
 * The core rule (spec §4): every gated request re-reads the database.
 * No session flag, no cookie, no token claim decides access.
 */
final class RequireSubscription implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $userId = Session::userId();

        if ($userId === null || !SubscriptionService::hasAccess($userId)) {
            return Response::redirect('/expired');
        }

        return $next();
    }
}
