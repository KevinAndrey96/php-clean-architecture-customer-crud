<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\Address;
use CustomerCrud\Domain\Customer\City;
use CustomerCrud\Domain\Customer\CompanyName;
use CustomerCrud\Domain\Customer\CreditLimit;
use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\InvalidCustomerField;
use CustomerCrud\Domain\Customer\Nit;
use CustomerCrud\Domain\Customer\PhoneNumber;

/**
 * Builds a Customer from raw input, validating every field before reporting so the form
 * can highlight all mistakes at once instead of one per round trip.
 */
final class CustomerAssembler
{
    /**
     * @throws ValidationFailed
     */
    public function assemble(CustomerInput $input): Customer
    {
        $errors = [];

        $nit = $this->attempt(static fn (): Nit => Nit::fromString($input->nit), $errors);
        $companyName = $this->attempt(static fn (): CompanyName => CompanyName::fromString($input->companyName), $errors);
        $address = $this->attempt(static fn (): Address => Address::fromString($input->address), $errors);
        $phone = $this->attempt(static fn (): PhoneNumber => PhoneNumber::fromString($input->phone), $errors);
        $city = $this->attempt(static fn (): City => City::fromString($input->city), $errors);
        $creditLimit = $this->attempt(static fn (): CreditLimit => CreditLimit::fromString($input->creditLimit), $errors);

        if ($nit === null || $companyName === null || $address === null || $phone === null || $city === null || $creditLimit === null) {
            /** @var non-empty-array<string, string> $errors */
            throw new ValidationFailed($errors);
        }

        return new Customer($nit, $companyName, $address, $phone, $city, $creditLimit);
    }

    /**
     * @template T of object
     *
     * @param callable(): T         $build
     * @param array<string, string> $errors
     *
     * @return T|null
     */
    private function attempt(callable $build, array &$errors): ?object
    {
        try {
            return $build();
        } catch (InvalidCustomerField $e) {
            $errors[$e->field] = $e->getMessage();

            return null;
        }
    }
}
