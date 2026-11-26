<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\CustomerRepository;

final class DeleteCustomer
{
    public function __construct(private readonly CustomerRepository $customers)
    {
    }

    /**
     * @throws CustomerNotFound
     */
    public function __invoke(string $nit): void
    {
        $this->customers->remove(NitLookup::parse($nit));
    }
}
