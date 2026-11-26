<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

/**
 * A business customer identified by its NIT. Every attribute is a validated value object,
 * so an instance can never hold the inconsistent rows the legacy forms let through.
 */
final class Customer
{
    public function __construct(
        public readonly Nit $nit,
        public readonly CompanyName $companyName,
        public readonly Address $address,
        public readonly PhoneNumber $phone,
        public readonly City $city,
        public readonly CreditLimit $creditLimit,
    ) {
    }
}
