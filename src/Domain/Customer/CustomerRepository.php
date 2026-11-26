<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

interface CustomerRepository
{
    /**
     * @throws DuplicateNit
     */
    public function add(Customer $customer): void;

    /**
     * Stores $customer in place of the record identified by $current, which allows the NIT
     * itself to change.
     *
     * @throws CustomerNotFound
     * @throws DuplicateNit
     */
    public function replace(Nit $current, Customer $customer): void;

    /**
     * @throws CustomerNotFound
     */
    public function remove(Nit $nit): void;

    /**
     * @throws CustomerNotFound
     */
    public function get(Nit $nit): Customer;

    /**
     * Case-insensitive match on NIT, company name or city, sorted by company name. An empty
     * term returns every customer.
     *
     * @return list<Customer>
     */
    public function search(string $term): array;
}
