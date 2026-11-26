<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

use RuntimeException;
use Throwable;

final class DuplicateNit extends RuntimeException
{
    public static function of(Nit $nit, ?Throwable $previous = null): self
    {
        return new self(sprintf('nit %s already registered', $nit->formatted()), 0, $previous);
    }
}
