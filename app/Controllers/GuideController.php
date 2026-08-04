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
            'isSubscribed' => $inApp || $this->isSubscribed(),
            'inApp'        => $inApp,
            'filters'      => $filters,
            'guides'       => $repo->filter($filters),
            'total'        => $repo->countFiltered($filters),
        ]);
    }

    private function renderShow(string $slug, bool $inApp): Response
    {
        $isSubscribed = $inApp || $this->isSubscribed();

        $repo  = new GuideRepo();

        // A guide flagged is_premium = 0 is free for everyone; only then is
        // body_bn selected for a signed-out reader.
        $meta = $repo->findBySlug($slug, false);
        if ($meta === null) {
            $this->notFound();
        }

        $mayReadBody = $isSubscribed || (int) $meta['is_premium'] === 0;
        $guide       = $mayReadBody ? $repo->findBySlug($slug, true) : $meta;

        $repo->incrementViews((int) $meta['id']);

        return $this->view('guides/show', [
            'isSubscribed' => $isSubscribed,
            'mayReadBody'  => $mayReadBody,
            'inApp'        => $inApp,
            'guide'        => $guide,
            'related'      => $repo->latest(3),
        ]);
    }
}
