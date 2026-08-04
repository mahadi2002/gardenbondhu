<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\PlantRepo;
use App\Repositories\UserPlantRepo;
use App\Services\CareScheduler;

/**
 * "আমার বাগান". Every query is scoped by user_id inside the repository;
 * a row that belongs to someone else is a 404, never a 403 (spec §10).
 */
final class GardenController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new UserPlantRepo();

        return $this->view('garden/index', [
            'plants' => $repo->forUser($userId),
            'dueToday' => $repo->dueTodayCount($userId),
        ]);
    }

    public function createForm(Request $request): Response
    {
        return $this->view('garden/create', [
            'plants'    => (new PlantRepo())->options(),
            'locations' => (array) config('content.space'),
        ]);
    }

    public function store(Request $request): Response
    {
        $validator = $this->validate($request);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/app/garden/add');
        }

        $userId = (int) $this->currentUserId();
        $repo   = new UserPlantRepo();

        $id = $repo->create($userId, [
            'plant_id'    => $validator->get('plant_id'),
            'nickname'    => $validator->get('nickname'),
            'planted_on'  => $validator->get('planted_on'),
            'location'    => $validator->get('location'),
            'pot_size_cm' => $validator->get('pot_size_cm'),
            'notes'       => $validator->get('notes'),
        ]);

        // Generate the first week of tasks straight away, so the schedule is
        // visible now rather than after tomorrow's cron.
        $row = $repo->find($id, $userId);
        if ($row !== null) {
            (new CareScheduler())->generateForPlant($row);
        }

        Session::notify('success', 'গাছটি যোগ হয়েছে। এই সপ্তাহের কাজের তালিকা তৈরি হয়ে গেছে।');

        return $this->redirect('/app/garden/' . $id);
    }

    public function show(Request $request, string $id): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new UserPlantRepo();

        $plant = $repo->find((int) $id, $userId);
        if ($plant === null) {
            $this->notFound();
        }

        return $this->view('garden/show', [
            'plant'     => $plant,
            'tasks'     => $repo->tasksForPlant((int) $id, $userId),
            'plants'    => (new PlantRepo())->options(),
            'locations' => (array) config('content.space'),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $userId    = (int) $this->currentUserId();
        $validator = $this->validate($request);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/app/garden/' . (int) $id);
        }

        $updated = (new UserPlantRepo())->update((int) $id, $userId, [
            'nickname'    => $validator->get('nickname'),
            'planted_on'  => $validator->get('planted_on'),
            'location'    => $validator->get('location'),
            'pot_size_cm' => $validator->get('pot_size_cm'),
            'notes'       => $validator->get('notes'),
            'is_archived' => $request->str('is_archived') === '1' ? 1 : 0,
        ]);

        if (!$updated) {
            $this->notFound();
        }

        Session::notify('success', 'তথ্য আপডেট হয়েছে।');

        return $this->redirect('/app/garden/' . (int) $id);
    }

    public function destroy(Request $request, string $id): Response
    {
        $userId = (int) $this->currentUserId();

        if (!(new UserPlantRepo())->delete((int) $id, $userId)) {
            $this->notFound();
        }

        Session::notify('success', 'গাছটি মুছে ফেলা হয়েছে।');

        return $this->redirect('/app/garden');
    }

    public function completeTask(Request $request, string $id): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new UserPlantRepo();

        $task = $repo->completeTask((int) $id, $userId);
        if ($task === null) {
            $this->notFound();
        }

        // Completing a task moves the plant's schedule forward.
        $plant = $repo->find((int) $task['user_plant_id'], $userId);
        if ($plant !== null) {
            (new CareScheduler())->generateForPlant($plant);
        }

        Session::notify('success', 'কাজটি সম্পন্ন হিসেবে চিহ্নিত হয়েছে।');

        return $this->back($request, '/app/garden');
    }

    private function validate(Request $request): Validator
    {
        return Validator::make($request->body, [
            'plant_id'    => 'int',
            'nickname'    => 'max:80',
            'planted_on'  => 'date',
            'location'    => 'required|in:balcony,rooftop,indoor,yard',
            'pot_size_cm' => 'int|between:5,200',
            'notes'       => 'max:2000',
        ], [
            'plant_id'    => 'গাছ',
            'nickname'    => 'ডাকনাম',
            'planted_on'  => 'রোপণের তারিখ',
            'location'    => 'জায়গা',
            'pot_size_cm' => 'টবের মাপ',
            'notes'       => 'নোট',
        ]);
    }
}
