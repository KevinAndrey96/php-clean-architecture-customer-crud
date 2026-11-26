<?php

declare(strict_types=1);

namespace CustomerCrud\Infrastructure\Persistence;

use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Domain\Customer\DuplicateNit;
use CustomerCrud\Domain\Customer\Nit;

final class InMemoryCustomerRepository implements CustomerRepository
{
    /** @var array<string, Customer> keyed by NIT body */
    private array $customers = [];

    public function add(Customer $customer): void
    {
        $key = $customer->nit->body();
        if (isset($this->customers[$key])) {
            throw DuplicateNit::of($customer->nit);
        }

        $this->customers[$key] = $customer;
    }

    public function replace(Nit $current, Customer $customer): void
    {
        $this->get($current);

        $key = $customer->nit->body();
        if (!$customer->nit->equals($current) && isset($this->customers[$key])) {
            throw DuplicateNit::of($customer->nit);
        }

        unset($this->customers[$current->body()]);
        $this->customers[$key] = $customer;
    }

    public function remove(Nit $nit): void
    {
        $this->get($nit);

        unset($this->customers[$nit->body()]);
    }

    public function get(Nit $nit): Customer
    {
        return $this->customers[$nit->body()] ?? throw CustomerNotFound::withNit($nit->formatted());
    }

    public function search(string $term): array
    {
        $needle = mb_strtolower($term);
        $matches = array_filter(
            $this->customers,
            static fn (Customer $c): bool => $needle === ''
                || str_contains($c->nit->body(), $needle)
                || str_contains(mb_strtolower($c->companyName->value()), $needle)
                || str_contains(mb_strtolower($c->city->value()), $needle),
        );

        usort(
            $matches,
            static fn (Customer $a, Customer $b): int => strcmp(
                mb_strtolower($a->companyName->value()),
                mb_strtolower($b->companyName->value()),
            ),
        );

        return $matches;
    }
}
