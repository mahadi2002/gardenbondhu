<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\PlantRepo;
use App\Repositories\ProblemRepo;
use App\Services\AuditService;

final class AdminPlantController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/plants/index', [
            'plants' => (new PlantRepo())->adminList($request->str('q')),
            'query'  => $request->str('q'),
        ]);
    }

    public function form(Request $request, ?string $id = null): Response
    {
        $repo  = new PlantRepo();
        $plant = $id === null ? null : $repo->findById((int) $id);

        if ($id !== null && $plant === null) {
            $this->notFound();
        }

        return $this->view('admin/plants/form', [
            'plant'      => $plant,
            'categories' => $repo->categories(),
            'seasons'    => Db::all('SELECT id, name_bn, bn_months FROM seasons ORDER BY id'),
            'plantSeasons' => $plant === null ? [] : $repo->seasonsFor((int) $plant['id']),
            'problems'   => (new ProblemRepo())->adminList('', 200),
            'linked'     => $plant === null ? [] : array_column($repo->problemsFor((int) $plant['id']), 'id'),
        ]);
    }

    public function store(Request $request): Response
    {
        return $this->persist($request, null);
    }

    public function update(Request $request, string $id): Response
    {
        if ((new PlantRepo())->findById((int) $id) === null) {
            $this->notFound();
        }
        return $this->persist($request, (int) $id);
    }

    public function destroy(Request $request, string $id): Response
    {
        (new PlantRepo())->delete((int) $id);

        AuditService::log('admin.plant.deleted', 'admin', Session::adminId(), 'plant', (int) $id, [], $request->ipHash());
        Session::notify('success', 'গাছটি মুছে ফেলা হয়েছে।');

        return $this->redirect('/admin/plants');
    }

    private function persist(Request $request, ?int $id): Response
    {
        $validator = Validator::make($request->body, [
            'slug'         => 'required|slug',
            'name_bn'      => 'required|max:120',
            'name_en'      => 'max:120',
            'scientific_name' => 'max:160',
            'category_id'  => 'int',
            'difficulty'   => 'required|in:easy,medium,hard',
            'sunlight'     => 'required|in:full,partial,shade',
            'water_need'   => 'required|in:low,medium,high',
            'growth_habit' => 'in:herb,shrub,tree,climber,succulent,grass',
            'summary_bn'   => 'required|min:40|max:2000',
            'water_interval_days' => 'int|between:1,60',
            'fertilizer_interval_days' => 'int|between:1,365',
            'harvest_days' => 'int|between:1,2000',
        ], [
            'slug'       => 'Slug',
            'name_bn'    => 'বাংলা নাম',
            'summary_bn' => 'সংক্ষিপ্ত বিবরণ',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect($id === null ? '/admin/plants/new' : '/admin/plants/' . $id);
        }

        // space_type is a SET column; only known members may be written.
        $spaces = array_values(array_intersect(
            $request->arr('space_type'),
            array_keys((array) config('content.space'))
        ));

        $data = $validator->validated() + [
            'space_type'    => implode(',', $spaces ?: ['pot']),
            'soil_mix'      => $request->str('soil_mix') ?: null,
            'pot_size_cm'   => $request->str('pot_size_cm') ?: null,
            'propagation'   => $request->str('propagation') ?: null,
            'fertilizer_note' => $request->str('fertilizer_note') ?: null,
            'pruning_note'  => $request->str('pruning_note') ?: null,
            'body_bn'       => $request->str('body_bn') ?: null,
            'hero_image'    => $request->str('hero_image') ?: null,
            'toxic_to_pets' => $request->str('toxic_to_pets') === '1' ? 1 : 0,
            'is_published'  => $request->str('is_published') === '1' ? 1 : 0,
        ];

        $repo    = new PlantRepo();
        $plantId = $repo->save($id, $data);

        $this->syncProblems($plantId, $request->arr('problems'));

        AuditService::log(
            $id === null ? 'admin.plant.created' : 'admin.plant.updated',
            'admin',
            Session::adminId(),
            'plant',
            $plantId,
            ['slug' => $data['slug']],
            $request->ipHash()
        );

        Session::notify('success', 'সংরক্ষিত হয়েছে।');

        return $this->redirect('/admin/plants/' . $plantId);
    }

    private function syncProblems(int $plantId, array $problemIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $problemIds))));

        Db::transaction(static function () use ($plantId, $ids): void {
            Db::exec('DELETE FROM plant_problems WHERE plant_id = ?', [$plantId]);
            foreach ($ids as $problemId) {
                Db::exec(
                    'INSERT IGNORE INTO plant_problems (plant_id, problem_id, frequency) VALUES (?, ?, "common")',
                    [$plantId, $problemId]
                );
            }
        });
    }
}
