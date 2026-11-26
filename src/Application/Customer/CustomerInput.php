<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\Address;
use CustomerCrud\Domain\Customer\City;
use CustomerCrud\Domain\Customer\CompanyName;
use CustomerCrud\Domain\Customer\CreditLimit;
use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\Nit;
use CustomerCrud\Domain\Customer\PhoneNumber;

/**
 * Raw form values exactly as typed, so an invalid submission can be shown back untouched.
 */
final class CustomerInput
{
    public function __construct(
        public readonly string $nit = '',
        public readonly string $companyName = '',
        public readonly string $address = '',
        public readonly string $phone = '',
        public readonly string $city = '',
        public readonly string $creditLimit = '',
    ) {
    }

    /**
     * @param array<array-key, mixed> $form
     */
    public static function fromArray(array $form): self
    {
        return new self(
            self::text($form, Nit::FIELD),
            self::text($form, CompanyName::FIELD),
            self::text($form, Address::FIELD),
            self::text($form, PhoneNumber::FIELD),
            self::text($form, City::FIELD),
            self::text($form, CreditLimit::FIELD),
        );
    }

    public static function fromCustomer(Customer $customer): self
    {
        return new self(
            $customer->nit->formatted(),
            $customer->companyName->value(),
            $customer->address->value(),
            $customer->phone->formatted(),
            $customer->city->value(),
            (string) $customer->creditLimit->pesos(),
        );
    }

    /**
     * @param array<array-key, mixed> $form
     */
    private static function text(array $form, string $field): string
    {
        $value = $form[$field] ?? '';

        return is_string($value) ? $value : '';
    }
}
