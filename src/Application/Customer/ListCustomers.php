<?php

declare(strict_types=1);

namespace CustomerCrud\Application\Customer;

use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerRepository;

final class ListCustomers
{
    private const MAX_TERM_LENGTH = 80;

    public function __construct(private readonly CustomerRepository $customers)
    {
    }

    /**
     * @return list<Customer>
     */
    public function __invoke(string $term = ''): array
    {
        $term = mb_substr(trim($term), 0, self::MAX_TERM_LENGTH);

        return $this->customers->search($term);
    }
}
