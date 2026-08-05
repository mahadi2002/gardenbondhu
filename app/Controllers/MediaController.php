<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\ImageService;

/**
 * Uploads live outside the web root and are served through PHP with a fixed
 * Content-Type, nosniff, and inline disposition — the web server never
 * executes anything a user uploaded, because it never has a route to it.
 */
final class MediaController extends Controller
{
    public function show(Request $request, string $token): Response
    {
        $service = new ImageService();
        $path    = ImageService::fromToken($token);

        if ($path === null) {
            $this->notFound();
        }

        $bytes = $service->read($path);
        if ($bytes === null) {
            $this->notFound();
        }

        return Response::file($bytes, $service->contentType());
    }
}
