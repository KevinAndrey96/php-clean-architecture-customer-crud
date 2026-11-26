<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Integration;

use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Infrastructure\Persistence\InMemoryCustomerRepository;

final class InMemoryCustomerRepositoryTest extends CustomerRepositoryContract
{
    protected function repository(): CustomerRepository
    {
        return new InMemoryCustomerRepository();
    }
}
