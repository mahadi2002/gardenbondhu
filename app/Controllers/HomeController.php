<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Validator;
use App\Repositories\ContactRepo;
use App\Repositories\GuideRepo;
use App\Repositories\PlantRepo;
use App\Repositories\ProblemRepo;
use App\Services\AuditService;
use App\Services\DiagnosisEngine;
use App\Services\Notifier;
use App\Services\SearchService;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->view('home/index', $this->homeData());
    }

    /**
     * The landing page's live diagnose demo. Posts here, re-renders the same
     * landing page with a real result inline — no login wall, no redirect to
     * a teaser. Anyone can see the tool actually work before registering.
     */
    public function diagnoseDemo(Request $request): Response
    {
        $engine     = new DiagnosisEngine();
        $symptomIds = $engine->validSymptomIds($request->arr('symptoms'));
        $plantId    = $request->int('plant_id') ?: null;

        $demoResults  = [];
        $demoAttempted = $symptomIds !== [];

        if ($symptomIds !== []) {
            $demoResults = $engine->diagnose($symptomIds, $plantId, 3);
            if ($demoResults === [] && $plantId !== null) {
                $demoResults = $engine->diagnose($symptomIds, null, 3);
            }
        }

        return $this->view('home/index', $this->homeData() + [
            'demoResults'   => $demoResults,
            'demoAttempted' => $demoAttempted,
            'demoSelected'  => $symptomIds,
        ]);
    }

    private function homeData(): array
    {
        $plants   = new PlantRepo();
        $problems = new ProblemRepo();

        return [
            'symptoms' => $problems->symptomsByBodyPart(),
            'stats'    => [
                'plants'   => $plants->publishedCount(),
                'problems' => $problems->publishedCount(),
                'guides'   => (new GuideRepo())->publishedCount(),
            ],
        ];
    }

    public function privacy(Request $request): Response
    {
        return $this->view('home/privacy');
    }

    public function terms(Request $request): Response
    {
        return $this->view('home/terms');
    }

    public function about(Request $request): Response
    {
        return $this->view('home/about');
    }

    public function contact(Request $request): Response
    {
        return $this->view('home/contact');
    }

    public function submitContact(Request $request): Response
    {
        // Honeypot: a real user never fills a field they cannot see.
        if ($request->str('website') !== '') {
            Session::notify('success', 'বার্তা পেয়েছি। আমরা শীঘ্রই যোগাযোগ করব।');
            return $this->redirect('/contact');
        }

        $validator = Validator::make($request->body, [
            'name'    => 'required|min:2|max:80',
            'contact' => 'required|min:5|max:120',
            'message' => 'required|min:10|max:2000',
        ], [
            'name'    => 'নাম',
            'contact' => 'যোগাযোগের নম্বর বা Email',
            'message' => 'বার্তা',
        ]);

        if ($validator->fails()) {
            $validator->flash();
            Session::notify('error', (string) $validator->firstError());
            return $this->redirect('/contact');
        }

        $name    = (string) $validator->get('name');
        $message = (string) $validator->get('message');
        $preview = str_excerpt($message, 120);

        $messageId = (new ContactRepo())->create($name, (string) $validator->get('contact'), $message, $request->ipHash());

        AuditService::log('contact.submitted', 'user', null, 'contact', $messageId, [
            'name'    => $name,
            'preview' => $preview,
        ], $request->ipHash());

        Notifier::contactReceived($messageId, $name, $preview);

        Session::notify('success', 'বার্তা পেয়েছি। আমরা শীঘ্রই যোগাযোগ করব।');

        return $this->redirect('/contact');
    }

    public function search(Request $request): Response
    {
        $query   = $request->str('q');
        $service = new SearchService();

        $results = $query === ''
            ? ['plants' => [], 'problems' => [], 'guides' => [], 'total' => 0]
            : $service->search($query);

        return $this->view('home/search', [
            'query'       => $query,
            'results'     => $results,
            'suggestions' => $results['total'] === 0 ? $service->suggestions() : [],
        ]);
    }

    /** Dynamic sitemap over published content only. */
    public function sitemap(Request $request): Response
    {
        $base = (string) config('app.url');

        $urls = [
            ['loc' => $base . '/',         'priority' => '1.0'],
            ['loc' => $base . '/plants',   'priority' => '0.8'],
            ['loc' => $base . '/problems', 'priority' => '0.8'],
            ['loc' => $base . '/guides',   'priority' => '0.8'],
            ['loc' => $base . '/about',    'priority' => '0.4'],
            ['loc' => $base . '/privacy',  'priority' => '0.3'],
            ['loc' => $base . '/terms',    'priority' => '0.3'],
            ['loc' => $base . '/contact',  'priority' => '0.3'],
        ];

        foreach ((new PlantRepo())->slugsForSitemap() as $row) {
            $urls[] = ['loc' => $base . '/plants/' . $row['slug'], 'lastmod' => $row['updated_at'], 'priority' => '0.7'];
        }
        foreach ((new ProblemRepo())->slugsForSitemap() as $row) {
            $urls[] = ['loc' => $base . '/problems/' . $row['slug'], 'lastmod' => $row['updated_at'], 'priority' => '0.7'];
        }
        foreach ((new GuideRepo())->slugsForSitemap() as $row) {
            $urls[] = ['loc' => $base . '/guides/' . $row['slug'], 'lastmod' => $row['updated_at'], 'priority' => '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
             . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . e($url['loc']) . '</loc>';
            if (isset($url['lastmod'])) {
                $xml .= '<lastmod>' . e(date('Y-m-d', strtotime((string) $url['lastmod']))) . '</lastmod>';
            }
            $xml .= '<priority>' . e($url['priority']) . '</priority></url>' . "\n";
        }

        return Response::text($xml . '</urlset>', 200, 'application/xml');
    }
}
