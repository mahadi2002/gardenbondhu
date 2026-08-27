<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\GuideRepo;

final class GuideController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->renderIndex($request, false);
    }

    public function appIndex(Request $request): Response
    {
        return $this->renderIndex($request, true);
    }

    public function show(Request $request, string $slug): Response
    {
        return $this->renderShow($slug, false);
    }

    public function appShow(Request $request, string $slug): Response
    {
        return $this->renderShow($slug, true);
    }

    private function renderIndex(Request $request, bool $inApp): Response
    {
        $repo    = new GuideRepo();
        $filters = array_filter([
            'category' => $request->str('category') ?: null,
            'q'        => $request->str('q') ?: null,
        ], static fn($v): bool => $v !== null);

        return $this->view('guides/index', [
            'isLoggedIn' => $inApp || $this->isLoggedIn(),
            'inApp'      => $inApp,
            'filters'    => $filters,
            'guides'     => $repo->filter($filters),
            'total'      => $repo->countFiltered($filters),
        ]);
    }

    private function renderShow(string $slug, bool $inApp): Response
    {
        $isLoggedIn = $inApp || $this->isLoggedIn();

        $repo  = new GuideRepo();
        $guide = $repo->findBySlug($slug);

        if ($guide === null) {
            $this->notFound();
        }

        $repo->incrementViews((int) $guide['id']);

        return $this->view('guides/show', [
            'isLoggedIn'  => $isLoggedIn,
            'mayReadBody' => $isLoggedIn,
            'inApp'       => $inApp,
            'guide'       => $guide,
            'related'     => $repo->latest(3),
        ]);
    }
}
