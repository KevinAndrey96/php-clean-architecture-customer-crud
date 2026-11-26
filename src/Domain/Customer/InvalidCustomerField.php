<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

use DomainException;

/**
 * Raised by a value object when the raw input for one customer field is not acceptable.
 */
final class InvalidCustomerField extends DomainException
{
    public function __construct(public readonly string $field, string $message)
    {
        parent::__construct($message);
    }
}
