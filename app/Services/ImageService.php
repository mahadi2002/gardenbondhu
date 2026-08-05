<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Crypto;
use App\Exceptions\HttpException;
use RuntimeException;

/**
 * Upload handling for Q&A photos.
 *
 * finfo check → GD re-encode (destroys any embedded payload and strips EXIF,
 * including GPS) → random filename → storage/uploads/YYYY/MM/ (never under
 * public/) → served back through MediaController with a fixed Content-Type.
 */
final class ImageService
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    private const MAX_EDGE = 1600;

    public const TYPE_ERROR = 'শুধু JPG, PNG বা WEBP ছবি দিন, সর্বোচ্চ ৪ MB।';

    /**
     * @param array $file one entry of $_FILES
     * @return string the stored relative path, e.g. "2026/08/a1b2….webp"
     */
    public function store(array $file): string
    {
        if (!function_exists('imagecreatefromstring')) {
            throw new RuntimeException('The GD extension is required for image uploads.');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new HttpException(422, self::TYPE_ERROR);
        }

        $maxBytes = (int) config('app.uploads.max_bytes', 4194304);
        if ((int) ($file['size'] ?? 0) <= 0 || (int) $file['size'] > $maxBytes) {
            throw new HttpException(422, self::TYPE_ERROR);
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new HttpException(422, self::TYPE_ERROR);
        }

        // Trust finfo, never the client-supplied MIME or the file extension.
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = (string) $finfo->file($tmp);

        if (!isset(self::ALLOWED[$mime])) {
            throw new HttpException(422, self::TYPE_ERROR);
        }

        $bytes = (string) file_get_contents($tmp);
        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            throw new HttpException(422, self::TYPE_ERROR);
        }

        $image = $this->resize($image);

        $dir  = date('Y/m');
        $name = Crypto::randomToken(16) . '.jpg';
        $abs  = $this->uploadRoot() . '/' . $dir;

        if (!is_dir($abs) && !@mkdir($abs, 0750, true) && !is_dir($abs)) {
            throw new RuntimeException('Could not create upload directory.');
        }

        // Re-encoding to JPEG is what actually neutralises a polyglot file:
        // only the decoded pixels survive, and EXIF is not carried over.
        if (!imagejpeg($image, $abs . '/' . $name, 82)) {
            imagedestroy($image);
            throw new RuntimeException('Could not write the uploaded image.');
        }

        imagedestroy($image);
        @chmod($abs . '/' . $name, 0640);

        return $dir . '/' . $name;
    }

    /** @param \GdImage $image */
    private function resize(\GdImage $image): \GdImage
    {
        $w = imagesx($image);
        $h = imagesy($image);

        if ($w <= self::MAX_EDGE && $h <= self::MAX_EDGE) {
            return $image;
        }

        $scale = self::MAX_EDGE / max($w, $h);
        $nw    = max(1, (int) round($w * $scale));
        $nh    = max(1, (int) round($h * $scale));

        $resized = imagecreatetruecolor($nw, $nh);
        imagefill($resized, 0, 0, imagecolorallocate($resized, 255, 255, 255));
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagedestroy($image);

        return $resized;
    }

    /** Read a stored upload back for MediaController. */
    public function read(string $relativePath): ?string
    {
        $abs = $this->resolve($relativePath);
        return $abs === null ? null : (string) file_get_contents($abs);
    }

    public function exists(string $relativePath): bool
    {
        return $this->resolve($relativePath) !== null;
    }

    /**
     * Resolve a stored path, refusing anything that escapes the upload root.
     * The token form is enforced first, so traversal never even reaches realpath.
     */
    private function resolve(string $relativePath): ?string
    {
        if (preg_match('#^\d{4}/\d{2}/[a-f0-9]{32}\.jpg$#', $relativePath) !== 1) {
            return null;
        }

        $abs  = $this->uploadRoot() . '/' . $relativePath;
        $real = realpath($abs);
        $root = realpath($this->uploadRoot());

        if ($real === false || $root === false || !str_starts_with($real, $root)) {
            return null;
        }

        return $real;
    }

    /** Everything served from here is re-encoded JPEG, so the type is fixed. */
    public function contentType(): string
    {
        return 'image/jpeg';
    }

    private function uploadRoot(): string
    {
        return APP_ROOT . '/' . trim((string) config('app.uploads.path', 'storage/uploads'), '/');
    }

    /**
     * Encode a stored path into the single URL segment the media route accepts:
     *   2026/08/abc….jpg  →  2026-08-abc…
     */
    public static function toToken(string $relativePath): string
    {
        return str_replace(['/', '.jpg'], ['-', ''], $relativePath);
    }

    public static function fromToken(string $token): ?string
    {
        if (preg_match('#^(\d{4})-(\d{2})-([a-f0-9]{32})$#', $token, $m) !== 1) {
            return null;
        }
        return $m[1] . '/' . $m[2] . '/' . $m[3] . '.jpg';
    }
}
