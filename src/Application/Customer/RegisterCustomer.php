<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Domain\Customer\DuplicateNit;
use CustomerCrud\Domain\Customer\Nit;

final class RegisterCustomer
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly CustomerAssembler $assembler,
    ) {
    }

    /**
     * @throws ValidationFailed
     */
    public function __invoke(CustomerInput $input): Customer
    {
        $customer = $this->assembler->assemble($input);

        try {
            $this->customers->add($customer);
        } catch (DuplicateNit $e) {
            throw new ValidationFailed([Nit::FIELD => sprintf('ya existe un cliente con el NIT %s', $customer->nit->formatted())], $e);
        }

        return $customer;
    }
}
