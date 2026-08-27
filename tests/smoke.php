<?php
declare(strict_types=1);

/**
 * CLI smoke test — no framework, no assertions library, just exit(1) on failure.
 * Run: php tests/smoke.php
 */

define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/app/bootstrap.php';

use App\Core\Crypto;
use App\Core\Csrf;
use App\Core\Db;
use App\Core\Markdown;
use App\Core\Validator;
use App\Repositories\PlantRepo;
use App\Repositories\ProblemRepo;
use App\Repositories\UserRepo;
use App\Support\Totp;

$failures = 0;

function check(string $label, bool $condition): void
{
    global $failures;
    if ($condition) {
        fwrite(STDOUT, "  OK  $label\n");
    } else {
        fwrite(STDOUT, "FAIL  $label\n");
        $GLOBALS['failures']++;
    }
}

fwrite(STDOUT, "== Crypto ==\n");
$plain = '01712345678';
$enc   = Crypto::encrypt($plain);
check('encrypt output is not plaintext', $enc !== $plain);
check('decrypt round-trips', Crypto::decrypt($enc) === $plain);
check('decrypt rejects tampered ciphertext', Crypto::decrypt(substr($enc, 0, -4) . 'abcd') === null);

$idx1 = Crypto::blindIndex($plain);
$idx2 = Crypto::blindIndex($plain);
check('blind index is stable', $idx1 === $idx2);
check('blind index is not reversible-looking (64 hex chars)', preg_match('/^[a-f0-9]{64}$/', $idx1) === 1);
check('blind index differs for different input', Crypto::blindIndex('01700000000') !== $idx1);

fwrite(STDOUT, "\n== CSRF ==\n");
session_id('smoke-test-session');
$_SESSION = [];
$token = Csrf::token();
check('token is generated', strlen($token) === 64);
check('token check passes for the real token', Csrf::check($token));
check('token check fails for a wrong token', !Csrf::check('wrong'));
check('token check fails for null', !Csrf::check(null));

fwrite(STDOUT, "\n== Validator ==\n");
$v = Validator::make(['email' => 'demo@kishalay.test'], ['email' => 'required|email']);
check('valid email passes', $v->passes());

$v2 = Validator::make(['email' => 'not-an-email'], ['email' => 'required|email']);
check('invalid email fails', $v2->fails());

fwrite(STDOUT, "\n== Markdown ==\n");
$html = Markdown::render("# শিরোনাম\n\nএকটি **গুরুত্বপূর্ণ** লাইন।\n\n- ক\n- খ");
check('renders heading', str_contains($html, '<h2>শিরোনাম</h2>'));
check('renders bold', str_contains($html, '<strong>গুরুত্বপূর্ণ</strong>'));
check('renders list', str_contains($html, '<li>ক</li>'));
check('strips raw script tags', !str_contains(Markdown::render('<script>alert(1)</script>'), '<script>'));

fwrite(STDOUT, "\n== Totp ==\n");
$totpSecret = Totp::generateSecret();
check('generated secret is base32 (A-Z2-7)', preg_match('/^[A-Z2-7]+$/', $totpSecret) === 1);

$now = time();
check('a freshly generated code verifies against its own secret', Totp::verify($totpSecret, Totp::code($totpSecret, $now), $now));
check('the wrong code is rejected', !Totp::verify($totpSecret, '000000', $now));
check('a non-numeric code is rejected', !Totp::verify($totpSecret, 'abcdef', $now));
check('one step of clock drift (+30s) still verifies', Totp::verify($totpSecret, Totp::code($totpSecret, $now), $now + 30));
check('two steps of clock drift (+60s) is rejected', !Totp::verify($totpSecret, Totp::code($totpSecret, $now), $now + 61));

// RFC 6238 Appendix B's own SHA-1 test vector: ASCII secret "12345678901234567890"
// (base32: GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ) at Unix time 59 produces the 8-digit
// TOTP "94287082" — our 6-digit truncation is that same value's last 6 digits.
check(
    'matches the RFC 6238 SHA-1 test vector',
    Totp::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ', 59) === '287082'
);

fwrite(STDOUT, "\n== Repositories & auth (email + password) ==\n");

// Only the connection attempt is guarded — a real assertion bug below should
// fail loudly, not get silently relabeled as "no database".
$pdo = null;
try {
    $pdo = Db::pdo();
} catch (\Throwable $e) {
    fwrite(STDOUT, "  SKIP  no working database connection (" . $e->getMessage() . ") — run `php database/migrate.php --fresh` first.\n");
}

if ($pdo !== null) {
    $pdo->beginTransaction();

    try {
        // Every logged-in user gets full content now — no paywall gating left
        // to test, so this just confirms the content actually round-trips.
        $plantSlug = 'smoke-test-plant-' . bin2hex(random_bytes(4));
        $plantRepo = new PlantRepo();
        $plantRepo->save(null, [
            'slug' => $plantSlug, 'name_bn' => 'পরীক্ষা গাছ', 'body_bn' => 'বিস্তারিত তথ্য',
            'difficulty' => 'easy', 'space_type' => 'pot', 'sunlight' => 'partial',
            'water_need' => 'medium', 'toxic_to_pets' => 0, 'is_published' => 1,
        ]);
        $plant = $plantRepo->findBySlug($plantSlug);
        check('PlantRepo returns full content', $plant !== null && ($plant['body_bn'] ?? null) === 'বিস্তারিত তথ্য');

        $problemSlug = 'smoke-test-problem-' . bin2hex(random_bytes(4));
        $problemRepo = new ProblemRepo();
        $problemRepo->save(null, [
            'slug' => $problemSlug, 'name_bn' => 'পরীক্ষা সমস্যা', 'type' => 'pest', 'severity' => 'low',
            'organic_remedy_bn' => 'প্রতিকার', 'is_published' => 1,
        ]);
        $problem = $problemRepo->findBySlug($problemSlug);
        check('ProblemRepo returns full content', $problem !== null && ($problem['organic_remedy_bn'] ?? null) === 'প্রতিকার');

        $email = 'smoke-test-' . bin2hex(random_bytes(4)) . '@example.test';
        $userRepo = new UserRepo();
        $user = $userRepo->create($email, password_hash('correct horse', PASSWORD_DEFAULT));
        check('UserRepo::create() persists the email', $userRepo->findByEmail($email)['email'] === $email);

        $forAuth = $userRepo->findForAuth($email);
        check('findForAuth() returns a verifiable password hash', password_verify('correct horse', (string) $forAuth['password_hash']));
    } finally {
        // Never persist test fixtures — roll back regardless of pass/fail.
        $pdo->rollBack();
    }
}

fwrite(STDOUT, "\n" . ($failures === 0 ? "All checks passed.\n" : "$failures check(s) FAILED.\n"));
exit($failures === 0 ? 0 : 1);
