<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\InvalidCustomerField;
use CustomerCrud\Domain\Customer\Nit;

/**
 * Parses a NIT that identifies an existing record (for example from a URL). A malformed
 * identifier cannot match any customer, so it is reported as not found.
 */
final class NitLookup
{
    /**
     * @throws CustomerNotFound
     */
    public static function parse(string $raw): Nit
    {
        try {
            return Nit::fromString($raw);
        } catch (InvalidCustomerField) {
            throw CustomerNotFound::withNit($raw);
        }
    }
}
