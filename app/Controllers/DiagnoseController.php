<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\PlantRepo;
use App\Repositories\ProblemRepo;
use App\Services\DiagnosisEngine;

/**
 * The signature feature. The leaf picker is progressive enhancement over a
 * plain form: every body part is a real <button>, every symptom a real
 * checkbox, so the whole flow works with the keyboard and without JS.
 */
final class DiagnoseController extends Controller
{
    public function wizard(Request $request): Response
    {
        $problems = new ProblemRepo();

        return $this->view('diagnose/wizard', [
            'symptoms'   => $problems->symptomsByBodyPart(),
            'bodyParts'  => (array) config('content.body_parts'),
            'plants'     => (new PlantRepo())->options(),
            'selected'   => [],
            'plantId'    => null,
        ]);
    }

    public function result(Request $request): Response
    {
        $engine   = new DiagnosisEngine();
        $problems = new ProblemRepo();

        $symptomIds = $engine->validSymptomIds($request->arr('symptoms'));
        $plantId    = $request->int('plant_id') ?: null;

        if ($symptomIds === []) {
            Session::notify('error', 'অন্তত একটি লক্ষণ বেছে নিন।');

            return $this->view('diagnose/wizard', [
                'symptoms'  => $problems->symptomsByBodyPart(),
                'bodyParts' => (array) config('content.body_parts'),
                'plants'    => (new PlantRepo())->options(),
                'selected'  => [],
                'plantId'   => $plantId,
            ], 422);
        }

        $results = $engine->diagnose($symptomIds, $plantId);

        // Nothing matched with the plant filter on: widen rather than dead-end.
        $widened = false;
        if ($results === [] && $plantId !== null) {
            $results = $engine->diagnose($symptomIds, null);
            $widened = $results !== [];
        }

        return $this->view('diagnose/result', [
            'results'      => $results,
            'widened'      => $widened,
            'chosen'       => $engine->namesFor($symptomIds),
            'symptomIds'   => $symptomIds,
            'plantId'      => $plantId,
            'plantName'    => $plantId === null ? null : (($p = (new PlantRepo())->findById($plantId)) ? $p['name_bn'] : null),
            'disclaimer'   => DiagnosisEngine::DISCLAIMER,
        ]);
    }
}
