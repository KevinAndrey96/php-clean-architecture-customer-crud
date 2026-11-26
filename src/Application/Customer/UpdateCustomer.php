<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Domain\Customer\DuplicateNit;
use CustomerCrud\Domain\Customer\Nit;

final class UpdateCustomer
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly CustomerAssembler $assembler,
    ) {
    }

    /**
     * The record is addressed by its current NIT, so the NIT itself can be corrected.
     *
     * @throws CustomerNotFound
     * @throws ValidationFailed
     */
    public function __invoke(string $currentNit, CustomerInput $input): Customer
    {
        $current = NitLookup::parse($currentNit);
        $customer = $this->assembler->assemble($input);

        try {
            $this->customers->replace($current, $customer);
        } catch (DuplicateNit $e) {
            throw new ValidationFailed([Nit::FIELD => sprintf('el NIT %s ya pertenece a otro cliente', $customer->nit->formatted())], $e);
        }

        return $customer;
    }
}
