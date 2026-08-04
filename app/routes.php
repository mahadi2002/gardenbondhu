<?php
declare(strict_types=1);

/**
 * THE route table (spec §3). Format: [method, path, 'Controller@action', [middleware]].
 *
 * Middleware keys: csrf | guest | auth | sub | admin | rl:<bucket>
 * SecurityHeaders is applied globally in public/index.php, not per route.
 *
 * Order matters: literal paths must precede {slug} patterns that would also match
 * (e.g. /app/plants/finder before /app/plants/{slug}).
 */
return [
    // ── Public ──────────────────────────────────────────────────────────
    ['GET',  '/',                    'HomeController@index',          []],
    ['GET',  '/privacy',             'HomeController@privacy',        []],
    ['GET',  '/terms',               'HomeController@terms',          []],
    ['GET',  '/about',               'HomeController@about',          []],
    ['GET',  '/contact',             'HomeController@contact',        []],
    ['POST', '/contact',             'HomeController@submitContact',  ['csrf', 'rl:contact']],
    ['GET',  '/search',              'HomeController@search',         ['rl:search']],
    ['GET',  '/sitemap.xml',         'HomeController@sitemap',        []],

    ['GET',  '/plants',              'PlantController@index',         []],
    ['GET',  '/plants/{slug}',       'PlantController@show',          []],
    ['GET',  '/problems',            'ProblemController@index',       []],
    ['GET',  '/problems/{slug}',     'ProblemController@show',        []],
    ['GET',  '/guides',              'GuideController@index',         []],
    ['GET',  '/guides/{slug}',       'GuideController@show',          []],

    // Uploaded images are never web-accessible; they are served through PHP (§9.5).
    ['GET',  '/media/{slug}',        'MediaController@show',          []],

    // ── Auth (the carrier billing provider OTP) ───────────────────────────────────────────────
    ['GET',  '/subscribe',           'AuthController@phoneForm',      ['guest']],
    ['POST', '/subscribe/otp',       'AuthController@requestOtp',     ['guest', 'csrf', 'rl:otp_request']],
    ['GET',  '/subscribe/verify',    'AuthController@otpForm',        ['guest']],
    ['POST', '/subscribe/verify',    'AuthController@verifyOtp',      ['guest', 'csrf', 'rl:otp_verify']],
    ['POST', '/subscribe/resend',    'AuthController@resendOtp',      ['guest', 'csrf', 'rl:otp_request']],
    ['GET',  '/login',               'AuthController@login',          []],
    ['POST', '/logout',              'AuthController@logout',         ['auth', 'csrf']],

    // ── Gated app ───────────────────────────────────────────────────────
    ['GET',  '/app',                       'DashboardController@index',   ['auth', 'sub']],
    ['GET',  '/app/plants',                'PlantController@appIndex',    ['auth', 'sub']],
    ['GET',  '/app/plants/finder',         'PlantController@finder',      ['auth', 'sub']],
    ['GET',  '/app/plants/{slug}',         'PlantController@appShow',     ['auth', 'sub']],
    ['GET',  '/app/problems',              'ProblemController@appIndex',  ['auth', 'sub']],
    ['GET',  '/app/problems/{slug}',       'ProblemController@appShow',   ['auth', 'sub']],
    ['GET',  '/app/guides',                'GuideController@appIndex',    ['auth', 'sub']],
    ['GET',  '/app/guides/{slug}',         'GuideController@appShow',     ['auth', 'sub']],

    ['GET',  '/app/diagnose',              'DiagnoseController@wizard',   ['auth', 'sub']],
    ['POST', '/app/diagnose',              'DiagnoseController@result',   ['auth', 'sub', 'csrf']],

    ['GET',  '/app/calendar',              'CalendarController@index',    ['auth', 'sub']],
    ['GET',  '/app/tools',                 'ToolController@index',        ['auth', 'sub']],
    ['POST', '/app/tools/water',           'ToolController@water',        ['auth', 'sub', 'csrf']],
    ['POST', '/app/tools/fertilizer',      'ToolController@fertilizer',   ['auth', 'sub', 'csrf']],
    ['POST', '/app/tools/pot',             'ToolController@pot',          ['auth', 'sub', 'csrf']],

    ['GET',  '/app/garden',                'GardenController@index',      ['auth', 'sub']],
    ['GET',  '/app/garden/add',            'GardenController@createForm', ['auth', 'sub']],
    ['POST', '/app/garden',                'GardenController@store',      ['auth', 'sub', 'csrf']],
    ['GET',  '/app/garden/{id}',           'GardenController@show',       ['auth', 'sub']],
    ['POST', '/app/garden/{id}',           'GardenController@update',     ['auth', 'sub', 'csrf']],
    ['POST', '/app/garden/{id}/delete',    'GardenController@destroy',    ['auth', 'sub', 'csrf']],
    ['POST', '/app/garden/task/{id}/done', 'GardenController@completeTask', ['auth', 'sub', 'csrf']],

    ['GET',  '/app/qa',                    'QaController@index',          ['auth', 'sub']],
    ['GET',  '/app/qa/ask',                'QaController@askForm',        ['auth', 'sub']],
    ['POST', '/app/qa',                    'QaController@store',          ['auth', 'sub', 'csrf', 'rl:qa_post']],
    ['GET',  '/app/qa/{id}',               'QaController@show',           ['auth', 'sub']],
    ['POST', '/app/qa/{id}/answer',        'QaController@answer',         ['auth', 'sub', 'csrf']],

    // ── Account (auth only — reachable while expired, so billing can be fixed) ──
    ['GET',  '/account',             'AccountController@index',           ['auth']],
    ['GET',  '/account/unsubscribe', 'AccountController@unsubscribeForm', ['auth']],
    ['POST', '/account/unsubscribe', 'AccountController@unsubscribe',     ['auth', 'csrf']],
    ['POST', '/account/delete',      'AccountController@destroy',         ['auth', 'csrf']],
    ['GET',  '/expired',             'AccountController@expired',         ['auth']],

    // ── Webhooks (no CSRF — signature + IP allowlist instead, §7.6) ─────
    ['POST', '/webhooks/carrier',     'WebhookController@carrier',          []],

    // ── Admin ───────────────────────────────────────────────────────────
    ['GET',  '/admin/login',         'Admin/AdminAuthController@form',    []],
    ['POST', '/admin/login',         'Admin/AdminAuthController@login',   ['csrf', 'rl:admin_login']],
    ['POST', '/admin/logout',        'Admin/AdminAuthController@logout',  ['admin', 'csrf']],
    ['GET',  '/admin',               'Admin/AdminDashboardController@index', ['admin']],

    ['GET',  '/admin/plants',            'Admin/AdminPlantController@index',   ['admin']],
    ['GET',  '/admin/plants/new',        'Admin/AdminPlantController@form',    ['admin']],
    ['POST', '/admin/plants',            'Admin/AdminPlantController@store',   ['admin', 'csrf']],
    ['GET',  '/admin/plants/{id}',       'Admin/AdminPlantController@form',    ['admin']],
    ['POST', '/admin/plants/{id}',       'Admin/AdminPlantController@update',  ['admin', 'csrf']],
    ['POST', '/admin/plants/{id}/delete', 'Admin/AdminPlantController@destroy', ['admin', 'csrf']],

    ['GET',  '/admin/problems',          'Admin/AdminProblemController@index',  ['admin']],
    ['GET',  '/admin/problems/new',      'Admin/AdminProblemController@form',   ['admin']],
    ['POST', '/admin/problems',          'Admin/AdminProblemController@store',  ['admin', 'csrf']],
    ['GET',  '/admin/problems/{id}',     'Admin/AdminProblemController@form',   ['admin']],
    ['POST', '/admin/problems/{id}',     'Admin/AdminProblemController@update', ['admin', 'csrf']],
    ['POST', '/admin/problems/{id}/delete', 'Admin/AdminProblemController@destroy', ['admin', 'csrf']],

    ['GET',  '/admin/guides',            'Admin/AdminGuideController@index',   ['admin']],
    ['GET',  '/admin/guides/new',        'Admin/AdminGuideController@form',    ['admin']],
    ['POST', '/admin/guides',            'Admin/AdminGuideController@store',   ['admin', 'csrf']],
    ['GET',  '/admin/guides/{id}',       'Admin/AdminGuideController@form',    ['admin']],
    ['POST', '/admin/guides/{id}',       'Admin/AdminGuideController@update',  ['admin', 'csrf']],
    ['POST', '/admin/guides/{id}/delete', 'Admin/AdminGuideController@destroy', ['admin', 'csrf']],

    ['GET',  '/admin/qa',                'Admin/AdminQaController@index',      ['admin']],
    ['GET',  '/admin/qa/{id}',           'Admin/AdminQaController@show',       ['admin']],
    ['POST', '/admin/qa/{id}',           'Admin/AdminQaController@update',     ['admin', 'csrf']],

    ['GET',  '/admin/users',             'Admin/AdminUserController@index',    ['admin']],
    ['GET',  '/admin/users/{id}',        'Admin/AdminUserController@show',     ['admin']],
    ['POST', '/admin/users/{id}',        'Admin/AdminUserController@update',   ['admin', 'csrf']],

    ['GET',  '/admin/logs',              'Admin/AdminDashboardController@logs', ['admin']],
];
