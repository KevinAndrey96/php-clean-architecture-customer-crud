<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use RuntimeException;
use Throwable;

/**
 * Every field-level problem of one submission, keyed by form field name.
 */
final class ValidationFailed extends RuntimeException
{
    /**
     * @param non-empty-array<string, string> $errors
     */
    public function __construct(public readonly array $errors, ?Throwable $previous = null)
    {
        parent::__construct('customer input: '.implode('; ', array_keys($errors)), 0, $previous);
    }
}
