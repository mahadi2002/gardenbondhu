<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Db;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Exceptions\HttpException;
use App\Repositories\PlantRepo;
use App\Repositories\QuestionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;
use App\Services\ImageService;

final class QaController extends Controller
{
    public function index(Request $request): Response
    {
        $page    = max(1, $request->int('page', 1));
        $perPage = 20;

        return $this->view('qa/index', [
            'questions' => (new QuestionRepo())->feed($perPage, ($page - 1) * $perPage, $request->str('q')),
            'query'     => $request->str('q'),
            'page'      => $page,
        ]);
    }

    public function askForm(Request $request): Response
    {
        return $this->view('qa/ask', [
            'plants'    => (new PlantRepo())->options(),
            'askedToday' => (new QuestionRepo())->askedTodayBy((int) $this->currentUserId()),
        ]);
    }

    public function store(Request $request): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new QuestionRepo();

        $validator = Validator::make($request->body, [
            'title'    => 'required|min:8|max:200',
            'body'     => 'required|min:20|max:4000',
            'plant_id' => 'int',
        ], [
            'title'    => 'প্রশ্নের শিরোনাম',
            'body'     => 'বিস্তারিত',
            'plant_id' => 'গাছ',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/app/qa/ask');
        }

        $imagePath = null;
        $file      = $request->file('image');

        if ($file !== null) {
            try {
                $imagePath = (new ImageService())->store($file);
            } catch (HttpException $e) {
                Session::flash('_errors', ['image' => [$e->getMessage()]]);
                $validator->flash();
                return $this->redirect('/app/qa/ask');
            }
        }

        $id = $repo->create($userId, [
            'plant_id' => $validator->get('plant_id'),
            'title'    => $validator->get('title'),
            'body'     => $validator->get('body'),
            'image'    => $imagePath,
        ]);

        AuditService::log('qa.asked', 'user', $userId, 'question', $id);

        Session::notify('success', 'প্রশ্নটি জমা হয়েছে। যাচাইয়ের পর এটি সবার জন্য দেখা যাবে।');

        return $this->redirect('/app/qa/' . $id);
    }

    public function show(Request $request, string $id): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new QuestionRepo();

        $question = $repo->find((int) $id, $userId);
        if ($question === null) {
            $this->notFound();
        }

        return $this->view('qa/show', [
            'question' => $question,
            'answers'  => $repo->answers((int) $id),
            'isOwner'  => (int) $question['user_id'] === $userId,
        ]);
    }

    public function answer(Request $request, string $id): Response
    {
        $userId = (int) $this->currentUserId();
        $repo   = new QuestionRepo();

        $question = $repo->find((int) $id, $userId);
        if ($question === null) {
            $this->notFound();
        }

        if (!in_array((string) $question['status'], ['approved', 'answered'], true)) {
            Session::notify('error', 'প্রশ্নটি এখনো যাচাই হয়নি, তাই উত্তর দেওয়া যাচ্ছে না।');
            return $this->redirect('/app/qa/' . (int) $id);
        }

        $validator = Validator::make($request->body, [
            'body' => 'required|min:15|max:4000',
        ], ['body' => 'উত্তর']);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/app/qa/' . (int) $id);
        }

        $user     = (new UserRepo())->find($userId);
        $isExpert = $user !== null && $user['role'] === 'expert';

        $repo->addAnswer((int) $id, $userId, null, (string) $validator->get('body'), $isExpert);

        Session::notify('success', 'উত্তর যোগ হয়েছে। ধন্যবাদ।');

        return $this->redirect('/app/qa/' . (int) $id);
    }
}
