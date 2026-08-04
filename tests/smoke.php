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
use App\Core\Markdown;
use App\Core\Validator;
use App\Support\Operators;

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

fwrite(STDOUT, "\n== Operators ==\n");
check('normalizes 8801XXXXXXXXX', Operators::normalize('8801712345678') === '01712345678');
check('normalizes +8801XXXXXXXXX', Operators::normalize('+8801812345678') === '01812345678');
check('rejects short numbers', Operators::normalize('012345') === null);
check('018 detected as robi', Operators::detect('01812345678') === 'robi');
check('016 detected as airtel', Operators::detect('01612345678') === 'airtel');
check('017 detected as grameenphone', Operators::detect('01712345678') === 'grameenphone');
check('robi is allowed', Operators::isAllowed('01812345678'));
check('airtel is allowed', Operators::isAllowed('01612345678'));
check('grameenphone is not allowed', !Operators::isAllowed('01712345678'));
check('mask hides the middle', Operators::mask('01712345678') === '01712****78');

fwrite(STDOUT, "\n== Validator ==\n");
$v = Validator::make(['msisdn' => '01712345678'], ['msisdn' => 'required|msisdn']);
check('valid msisdn passes', $v->passes());

$v2 = Validator::make(['msisdn' => '123'], ['msisdn' => 'required|msisdn']);
check('invalid msisdn fails', $v2->fails());
check('invalid msisdn has a Bangla message', str_contains((string) $v2->firstError(), 'সংখ্যার'));

fwrite(STDOUT, "\n== Markdown ==\n");
$html = Markdown::render("# শিরোনাম\n\nএকটি **গুরুত্বপূর্ণ** লাইন।\n\n- ক\n- খ");
check('renders heading', str_contains($html, '<h2>শিরোনাম</h2>'));
check('renders bold', str_contains($html, '<strong>গুরুত্বপূর্ণ</strong>'));
check('renders list', str_contains($html, '<li>ক</li>'));
check('strips raw script tags', !str_contains(Markdown::render('<script>alert(1)</script>'), '<script>'));

fwrite(STDOUT, "\n" . ($failures === 0 ? "All checks passed.\n" : "$failures check(s) FAILED.\n"));
exit($failures === 0 ? 0 : 1);
