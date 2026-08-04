<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\GuideRepo;
use App\Services\AuditService;

final class AdminGuideController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('admin/guides/index', [
            'guides' => (new GuideRepo())->adminList($request->str('q')),
            'query'  => $request->str('q'),
        ]);
    }

    public function form(Request $request, ?string $id = null): Response
    {
        $guide = $id === null ? null : (new GuideRepo())->findById((int) $id);

        if ($id !== null && $guide === null) {
            $this->notFound();
        }

        return $this->view('admin/guides/form', ['guide' => $guide]);
    }

    public function store(Request $request): Response
    {
        return $this->persist($request, null);
    }

    public function update(Request $request, string $id): Response
    {
        if ((new GuideRepo())->findById((int) $id) === null) {
            $this->notFound();
        }
        return $this->persist($request, (int) $id);
    }

    public function destroy(Request $request, string $id): Response
    {
        (new GuideRepo())->delete((int) $id);

        AuditService::log('admin.guide.deleted', 'admin', Session::adminId(), 'guide', (int) $id, [], $request->ipHash());
        Session::notify('success', 'মুছে ফেলা হয়েছে।');

        return $this->redirect('/admin/guides');
    }

    private function persist(Request $request, ?int $id): Response
    {
        $validator = Validator::make($request->body, [
            'slug'       => 'required|slug',
            'title_bn'   => 'required|max:200',
            'category'   => 'required|in:basic,soil,water,fertilizer,pest,propagation,tools,rooftop,indoor,season',
            'excerpt_bn' => 'required|min:40|max:2000',
            'read_minutes' => 'int|between:1,120',
        ], [
            'slug'       => 'Slug',
            'title_bn'   => 'শিরোনাম',
            'excerpt_bn' => 'সংক্ষিপ্ত বিবরণ',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect($id === null ? '/admin/guides/new' : '/admin/guides/' . $id);
        }

        $published = $request->str('is_published') === '1';

        $data = $validator->validated() + [
            'body_bn'      => $request->str('body_bn') ?: null,
            'is_premium'   => $request->str('is_premium') === '0' ? 0 : 1,
            'is_published' => $published ? 1 : 0,
            'published_at' => $published ? ($request->str('published_at') ?: date('Y-m-d H:i:s')) : null,
        ];

        $guideId = (new GuideRepo())->save($id, $data);

        AuditService::log(
            $id === null ? 'admin.guide.created' : 'admin.guide.updated',
            'admin',
            Session::adminId(),
            'guide',
            $guideId,
            ['slug' => $data['slug']],
            $request->ipHash()
        );

        Session::notify('success', 'সংরক্ষিত হয়েছে।');

        return $this->redirect('/admin/guides/' . $guideId);
    }
}
