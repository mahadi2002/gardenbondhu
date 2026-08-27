<?php
declare(strict_types=1);

/**
 * The route table. Format: [method, path, 'Controller@action', [middleware]].
 *
 * Middleware keys: csrf | guest | auth | admin | rl:<bucket>
 * SecurityHeaders is applied globally in public/index.php, not per route.
 *
 * Order matters: literal paths must precede {slug} patterns that would also match
 * (e.g. /app/plants/finder before /app/plants/{slug}).
 */
return [
    // ── Ops ─────────────────────────────────────────────────────────────
    ['GET',  '/health',              'HealthController@check',        []],

    // ── Public ──────────────────────────────────────────────────────────
    ['GET',  '/',                    'HomeController@index',          []],
    ['POST', '/diagnose',            'HomeController@diagnoseDemo',   ['csrf', 'rl:diagnose_demo']],
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

    // Uploaded images are never web-accessible; they are served through PHP.
    ['GET',  '/media/{slug}',        'MediaController@show',          []],

    // ── Auth (email + password) ────────────────────────────────────────
    ['GET',  '/register',            'AuthController@registerForm',   ['guest']],
    ['POST', '/register',            'AuthController@register',       ['guest', 'csrf', 'rl:register']],
    ['GET',  '/login',               'AuthController@loginForm',      ['guest']],
    ['POST', '/login',               'AuthController@login',          ['guest', 'csrf', 'rl:login']],
    ['POST', '/logout',              'AuthController@logout',         ['auth', 'csrf']],
    ['GET',  '/forgot-password',     'AuthController@forgotPasswordForm', ['guest']],
    ['POST', '/forgot-password',     'AuthController@forgotPassword', ['guest', 'csrf', 'rl:password_reset']],
    ['GET',  '/reset-password/{slug}', 'AuthController@resetPasswordForm', ['guest']],
    ['POST', '/reset-password/{slug}', 'AuthController@resetPassword',     ['guest', 'csrf', 'rl:password_reset']],

    // ── Gated app (any logged-in user — no billing gate) ──────────────────
    ['GET',  '/app',                       'DashboardController@index',   ['auth']],
    ['GET',  '/app/plants',                'PlantController@appIndex',    ['auth']],
    ['GET',  '/app/plants/finder',         'PlantController@finder',      ['auth']],
    ['GET',  '/app/plants/{slug}',         'PlantController@appShow',     ['auth']],
    ['GET',  '/app/problems',              'ProblemController@appIndex',  ['auth']],
    ['GET',  '/app/problems/{slug}',       'ProblemController@appShow',   ['auth']],
    ['GET',  '/app/guides',                'GuideController@appIndex',    ['auth']],
    ['GET',  '/app/guides/{slug}',         'GuideController@appShow',     ['auth']],

    ['GET',  '/app/diagnose',              'DiagnoseController@wizard',   ['auth']],
    ['POST', '/app/diagnose',              'DiagnoseController@result',   ['auth', 'csrf']],

    ['GET',  '/app/calendar',              'CalendarController@index',    ['auth']],
    ['GET',  '/app/tools',                 'ToolController@index',        ['auth']],
    ['POST', '/app/tools/water',           'ToolController@water',        ['auth', 'csrf']],
    ['POST', '/app/tools/fertilizer',      'ToolController@fertilizer',   ['auth', 'csrf']],
    ['POST', '/app/tools/pot',             'ToolController@pot',          ['auth', 'csrf']],

    ['GET',  '/app/garden',                'GardenController@index',      ['auth']],
    ['GET',  '/app/garden/add',            'GardenController@createForm', ['auth']],
    ['POST', '/app/garden',                'GardenController@store',      ['auth', 'csrf']],
    ['GET',  '/app/garden/{id}',           'GardenController@show',       ['auth']],
    ['POST', '/app/garden/{id}',           'GardenController@update',     ['auth', 'csrf']],
    ['POST', '/app/garden/{id}/delete',    'GardenController@destroy',    ['auth', 'csrf']],
    ['POST', '/app/garden/task/{id}/done', 'GardenController@completeTask', ['auth', 'csrf']],

    ['GET',  '/app/qa',                    'QaController@index',          ['auth']],
    ['GET',  '/app/qa/ask',                'QaController@askForm',        ['auth']],
    ['POST', '/app/qa',                    'QaController@store',          ['auth', 'csrf', 'rl:qa_post']],
    ['GET',  '/app/qa/{id}',               'QaController@show',           ['auth']],
    ['POST', '/app/qa/{id}/answer',        'QaController@answer',         ['auth', 'csrf']],

    // ── Account ─────────────────────────────────────────────────────────
    ['GET',  '/account',             'AccountController@index',           ['auth']],
    ['POST', '/account/delete',      'AccountController@destroy',         ['auth', 'csrf']],

    // ── Admin ───────────────────────────────────────────────────────────
    ['GET',  '/admin/login',         'Admin/AdminAuthController@form',    []],
    ['POST', '/admin/login',         'Admin/AdminAuthController@login',   ['csrf', 'rl:admin_login']],
    ['GET',  '/admin/login/verify',  'Admin/AdminAuthController@verifyForm', []],
    ['POST', '/admin/login/verify',  'Admin/AdminAuthController@verify',  ['csrf', 'rl:admin_totp']],
    ['POST', '/admin/logout',        'Admin/AdminAuthController@logout',  ['admin', 'csrf']],
    ['GET',  '/admin',               'Admin/AdminDashboardController@index', ['admin']],

    ['GET',  '/admin/security',              'Admin/AdminSecurityController@index',        ['admin']],
    ['GET',  '/admin/security/totp/setup',   'Admin/AdminSecurityController@setup',        ['admin']],
    ['POST', '/admin/security/totp/confirm', 'Admin/AdminSecurityController@confirm',      ['admin', 'csrf', 'rl:admin_totp']],
    ['POST', '/admin/security/totp/disable', 'Admin/AdminSecurityController@disable',      ['admin', 'csrf']],

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

    ['GET',  '/admin/contact',           'Admin/AdminContactController@index',   ['admin']],
    ['GET',  '/admin/contact/{id}',      'Admin/AdminContactController@show',    ['admin']],
    ['POST', '/admin/contact/{id}/resolve', 'Admin/AdminContactController@resolve', ['admin', 'csrf']],

    ['GET',  '/admin/logs',              'Admin/AdminDashboardController@logs', ['admin']],
];
