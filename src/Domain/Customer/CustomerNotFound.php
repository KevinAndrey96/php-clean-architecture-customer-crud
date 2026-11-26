<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

use RuntimeException;

final class CustomerNotFound extends RuntimeException
{
    public static function withNit(string $nit): self
    {
        return new self(sprintf('customer %s not found', $nit));
    }
}
