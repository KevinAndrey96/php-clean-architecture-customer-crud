<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Integration;

use CustomerCrud\Domain\Customer\Customer;
use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Domain\Customer\DuplicateNit;
use CustomerCrud\Domain\Customer\Nit;
use CustomerCrud\Tests\Support\Customers;
use PHPUnit\Framework\TestCase;

/**
 * Behaviour every CustomerRepository must share, run against each implementation.
 */
abstract class CustomerRepositoryContract extends TestCase
{
    abstract protected function repository(): CustomerRepository;

    public function testRoundTripsEveryField(): void
    {
        $repository = $this->repository();
        $customer = Customers::customer();

        $repository->add($customer);
        $stored = $repository->get(Nit::fromString('900123456'));

        self::assertSame($customer->nit->formatted(), $stored->nit->formatted());
        self::assertSame($customer->companyName->value(), $stored->companyName->value());
        self::assertSame($customer->address->value(), $stored->address->value());
        self::assertSame($customer->phone->digits(), $stored->phone->digits());
        self::assertSame($customer->city->value(), $stored->city->value());
        self::assertSame($customer->creditLimit->pesos(), $stored->creditLimit->pesos());
    }

    public function testRejectsADuplicateNit(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer());

        $this->expectException(DuplicateNit::class);
        $repository->add(Customers::customer(['company_name' => 'Otra Empresa S.A.']));
    }

    public function testReplaceMovesTheRecordToANewNit(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer());

        $repository->replace(Nit::fromString('900123456'), Customers::customer(['nit' => '800197268', 'city' => 'Cali']));

        self::assertSame('Cali', $repository->get(Nit::fromString('800197268'))->city->value());
        $this->expectException(CustomerNotFound::class);
        $repository->get(Nit::fromString('900123456'));
    }

    public function testReplaceWithUnchangedValuesSucceeds(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer());

        $repository->replace(Nit::fromString('900123456'), Customers::customer());

        self::assertCount(1, $repository->search(''));
    }

    public function testReplaceOntoAnotherCustomersNitIsADuplicate(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer());
        $repository->add(Customers::customer(['nit' => '800197268']));

        $this->expectException(DuplicateNit::class);
        $repository->replace(Nit::fromString('900123456'), Customers::customer(['nit' => '800197268']));
    }

    public function testReplaceOfAMissingCustomerIsNotFound(): void
    {
        $this->expectException(CustomerNotFound::class);
        $this->repository()->replace(Nit::fromString('900123456'), Customers::customer());
    }

    public function testRemove(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer());

        $repository->remove(Nit::fromString('900123456'));

        self::assertSame([], $repository->search(''));
        $this->expectException(CustomerNotFound::class);
        $repository->remove(Nit::fromString('900123456'));
    }

    public function testSearchIsCaseInsensitiveSortedAndTreatsWildcardsLiterally(): void
    {
        $repository = $this->repository();
        $repository->add(Customers::customer(['nit' => '830045678', 'company_name' => 'zeta 100% Industrial', 'city' => 'Cali']));
        $repository->add(Customers::customer(['nit' => '811022233', 'company_name' => 'Alfa_Beta S.A.S.', 'city' => 'Medellín']));
        $repository->add(Customers::customer());

        self::assertSame(['Alfa_Beta S.A.S.', 'Andes Logística S.A.S.', 'zeta 100% Industrial'], self::names($repository->search('')));
        self::assertSame(['zeta 100% Industrial'], self::names($repository->search('ZETA')));
        self::assertSame(['zeta 100% Industrial'], self::names($repository->search('100%')));
        self::assertSame(['Alfa_Beta S.A.S.'], self::names($repository->search('a_b')));
        self::assertSame([], self::names($repository->search('a%z')));
        self::assertSame(['Andes Logística S.A.S.'], self::names($repository->search('900123')));
    }

    /**
     * @param list<Customer> $customers
     *
     * @return list<string>
     */
    private static function names(array $customers): array
    {
        return array_map(static fn (Customer $c): string => $c->companyName->value(), $customers);
    }
}
