<?php
declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * A user-facing OTP failure. `$reason` maps to a row of the §10 error matrix;
 * `$message` is the exact Bangla string shown to the user.
 */
class OtpException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        string $message,
        public readonly int $attemptsLeft = 0,
    ) {
        parent::__construct($message);
    }
}
