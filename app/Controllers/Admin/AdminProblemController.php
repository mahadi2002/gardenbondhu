<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\ProblemRepo;
use App\Services\AuditService;

final class AdminProblemController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/problems/index', [
            'problems' => (new ProblemRepo())->adminList($request->str('q')),
            'query'    => $request->str('q'),
        ]);
    }

    public function form(Request $request, ?string $id = null): Response
    {
        $repo    = new ProblemRepo();
        $problem = $id === null ? null : $repo->findById((int) $id);

        if ($id !== null && $problem === null) {
            $this->notFound();
        }

        $weights = [];
        if ($problem !== null) {
            foreach ($repo->symptomsFor((int) $problem['id']) as $row) {
                $weights[(int) $row['id']] = (int) $row['weight'];
            }
        }

        return $this->view('admin/problems/form', [
            'problem'  => $problem,
            'symptoms' => $repo->allSymptoms(),
            'weights'  => $weights,
        ]);
    }

    public function store(Request $request): Response
    {
        return $this->persist($request, null);
    }

    public function update(Request $request, string $id): Response
    {
        if ((new ProblemRepo())->findById((int) $id) === null) {
            $this->notFound();
        }
        return $this->persist($request, (int) $id);
    }

    public function destroy(Request $request, string $id): Response
    {
        (new ProblemRepo())->delete((int) $id);

        AuditService::log('admin.problem.deleted', 'admin', Session::adminId(), 'problem', (int) $id, [], $request->ipHash());
        Session::notify('success', 'মুছে ফেলা হয়েছে।');

        return $this->redirect('/admin/problems');
    }

    private function persist(Request $request, ?int $id): Response
    {
        $validator = Validator::make($request->body, [
            'slug'           => 'required|slug',
            'name_bn'        => 'required|max:160',
            'name_en'        => 'max:160',
            'type'           => 'required|in:pest,fungal,bacterial,viral,nutrient,cultural,environmental',
            'severity'       => 'required|in:low,medium,high',
            'description_bn' => 'required|min:30|max:2000',
            'safety_note_bn' => 'max:500',
        ], [
            'slug'           => 'Slug',
            'name_bn'        => 'বাংলা নাম',
            'description_bn' => 'বিবরণ',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect($id === null ? '/admin/problems/new' : '/admin/problems/' . $id);
        }

        $data = $validator->validated() + [
            'identification_bn'  => $request->str('identification_bn') ?: null,
            'organic_remedy_bn'  => $request->str('organic_remedy_bn') ?: null,
            'chemical_remedy_bn' => $request->str('chemical_remedy_bn') ?: null,
            'prevention_bn'      => $request->str('prevention_bn') ?: null,
            'is_published'       => $request->str('is_published') === '1' ? 1 : 0,
        ];

        $repo      = new ProblemRepo();
        $problemId = $repo->save($id, $data);

        // symptom_weights[<symptom_id>] = 1..10; zero or missing means unlinked.
        $weights = [];
        foreach ($request->input('symptom_weights', []) as $symptomId => $weight) {
            if (is_numeric($symptomId) && is_numeric($weight) && (int) $weight > 0) {
                $weights[(int) $symptomId] = (int) $weight;
            }
        }
        $repo->syncSymptoms($problemId, $weights);

        AuditService::log(
            $id === null ? 'admin.problem.created' : 'admin.problem.updated',
            'admin',
            Session::adminId(),
            'problem',
            $problemId,
            ['slug' => $data['slug']],
            $request->ipHash()
        );

        Session::notify('success', 'সংরক্ষিত হয়েছে।');

        return $this->redirect('/admin/problems/' . $problemId);
    }
}
