<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Repositories\PlantRepo;

/**
 * Public and gated routes render the SAME view with an $isLoggedIn flag —
 * one template, so the free-preview and full-account experience can never
 * quietly drift apart. Full content (soil mix, fertilizer notes, etc.) only
 * renders once a viewer is logged in — free registration, not a paywall.
 */
final class PlantController extends Controller
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

    /** The finder is a gated feature; the public list is a plain catalogue. */
    public function finder(Request $request): Response
    {
        $repo    = new PlantRepo();
        $filters = $this->filtersFrom($request);

        return $this->view('plants/finder', [
            'isLoggedIn'   => true,
            'inApp'        => true,
            'filters'      => $filters,
            'categories'   => $repo->categories(),
            'plants'       => $repo->filter($filters, $request->str('sort', 'name_bn')),
            'total'        => $repo->countFiltered($filters),
        ]);
    }

    private function renderIndex(Request $request, bool $inApp): Response
    {
        $repo    = new PlantRepo();
        $filters = $this->filtersFrom($request);
        $page    = max(1, $request->int('page', 1));
        $perPage = 24;

        return $this->view('plants/index', [
            'isLoggedIn'   => $inApp || $this->isLoggedIn(),
            'inApp'        => $inApp,
            'filters'      => $filters,
            'categories'   => $repo->categories(),
            'plants'       => $repo->filter($filters, $request->str('sort', 'name_bn'), $perPage, ($page - 1) * $perPage),
            'total'        => $repo->countFiltered($filters),
            'page'         => $page,
            'perPage'      => $perPage,
        ]);
    }

    private function renderShow(string $slug, bool $inApp): Response
    {
        // The gated route has already passed RequireAuth, so $inApp implies
        // logged in. The public route re-checks the session for a signed-in
        // visitor, so they never hit the free-preview cut on their own account.
        $isLoggedIn = $inApp || $this->isLoggedIn();

        $repo  = new PlantRepo();
        $plant = $repo->findBySlug($slug);

        if ($plant === null) {
            $this->notFound();
        }

        $repo->incrementViews((int) $plant['id']);

        return $this->view('plants/show', [
            'isLoggedIn' => $isLoggedIn,
            'inApp'      => $inApp,
            'plant'      => $plant,
            'seasons'    => $repo->seasonsFor((int) $plant['id']),
            'problems'   => $repo->problemsFor((int) $plant['id']),
            'related'    => $repo->related((int) $plant['id'], $plant['category_id'] === null ? null : (int) $plant['category_id']),
        ]);
    }

    /** Whitelist the query string into the filter shape the repo expects. */
    private function filtersFrom(Request $request): array
    {
        return array_filter([
            'category_id'   => $request->int('category_id') ?: null,
            'sunlight'      => $request->str('sunlight') ?: null,
            'water_need'    => $request->str('water_need') ?: null,
            'difficulty'    => $request->str('difficulty') ?: null,
            'space_type'    => $request->str('space_type') ?: null,
            'toxic_to_pets' => $request->str('toxic_to_pets') === '0' ? '0' : null,
            'q'             => $request->str('q') ?: null,
        ], static fn($v): bool => $v !== null);
    }
}
