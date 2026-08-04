<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\QuestionRepo;
use App\Repositories\UserRepo;
use App\Services\AuditService;

final class AdminQaController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->str('status', 'pending');
        if (!in_array($status, ['pending', 'approved', 'answered', 'rejected'], true)) {
            $status = 'pending';
        }

        return $this->view('admin/qa/index', [
            'questions' => (new QuestionRepo())->moderationQueue($status),
            'status'    => $status,
        ]);
    }

    public function show(Request $request, string $id): Response
    {
        $repo     = new QuestionRepo();
        $question = $repo->findForAdmin((int) $id);

        if ($question === null) {
            $this->notFound();
        }

        return $this->view('admin/qa/show', [
            'question' => $question,
            'answers'  => $repo->answers((int) $id),
        ]);
    }

    public function update(Request $request, string $id): Response
    {
        $repo     = new QuestionRepo();
        $question = $repo->findForAdmin((int) $id);

        if ($question === null) {
            $this->notFound();
        }

        $action  = $request->str('action');
        $adminId = Session::adminId();

        switch ($action) {
            case 'approve':
                $repo->setStatus((int) $id, (int) $question['answer_count'] > 0 ? 'answered' : 'approved');
                Session::notify('success', 'প্রশ্নটি অনুমোদিত হয়েছে।');
                break;

            case 'reject':
                $repo->setStatus((int) $id, 'rejected');
                Session::notify('success', 'প্রশ্নটি বাতিল করা হয়েছে।');
                break;

            case 'answer':
                $body = $request->str('body');
                if (mb_strlen($body, 'UTF-8') < 15) {
                    Session::notify('error', 'উত্তর কমপক্ষে ১৫ অক্ষরের হতে হবে।');
                    return $this->redirect('/admin/qa/' . (int) $id);
                }
                $repo->addAnswer((int) $id, null, $adminId, $body, true);
                if ($question['status'] === 'pending') {
                    $repo->setStatus((int) $id, 'answered');
                }
                Session::notify('success', 'উত্তর যোগ হয়েছে।');
                break;

            case 'mark_expert':
                (new UserRepo())->setRole((int) $question['user_id'], 'expert');
                Session::notify('success', 'ব্যবহারকারীকে বিশেষজ্ঞ হিসেবে চিহ্নিত করা হয়েছে।');
                break;

            default:
                Session::notify('error', 'অজানা কাজ।');
                return $this->redirect('/admin/qa/' . (int) $id);
        }

        AuditService::log('admin.qa.' . $action, 'admin', $adminId, 'question', (int) $id, [], $request->ipHash());

        return $this->redirect('/admin/qa/' . (int) $id);
    }
}
