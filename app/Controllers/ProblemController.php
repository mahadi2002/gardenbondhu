<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\ProblemRepo;

final class ProblemController extends Controller
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
        $repo    = new ProblemRepo();
        $filters = array_filter([
            'type'     => $request->str('type') ?: null,
            'severity' => $request->str('severity') ?: null,
            'q'        => $request->str('q') ?: null,
        ], static fn($v): bool => $v !== null);

        $page    = max(1, $request->int('page', 1));
        $perPage = 24;

        return $this->view('problems/index', [
            'isSubscribed' => $inApp || $this->isSubscribed(),
            'inApp'        => $inApp,
            'filters'      => $filters,
            'problems'     => $repo->filter($filters, $perPage, ($page - 1) * $perPage),
            'total'        => $repo->countFiltered($filters),
            'page'         => $page,
            'perPage'      => $perPage,
        ]);
    }

    private function renderShow(string $slug, bool $inApp): Response
    {
        $isSubscribed = $inApp || $this->isSubscribed();

        $repo    = new ProblemRepo();
        $problem = $repo->findBySlug($slug, $isSubscribed);

        if ($problem === null) {
            $this->notFound();
        }

        return $this->view('problems/show', [
            'isSubscribed' => $isSubscribed,
            'inApp'        => $inApp,
            'problem'      => $problem,
            'symptoms'     => $repo->symptomsFor((int) $problem['id']),
            'plants'       => $repo->plantsFor((int) $problem['id']),
        ]);
    }
}
