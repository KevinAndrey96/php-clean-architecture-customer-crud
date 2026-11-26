<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Unit\Application;

use CustomerCrud\Application\Customer\CustomerAssembler;
use CustomerCrud\Application\Customer\CustomerInput;
use CustomerCrud\Application\Customer\DeleteCustomer;
use CustomerCrud\Application\Customer\GetCustomer;
use CustomerCrud\Application\Customer\ListCustomers;
use CustomerCrud\Application\Customer\RegisterCustomer;
use CustomerCrud\Application\Customer\UpdateCustomer;
use CustomerCrud\Application\Customer\ValidationFailed;
use CustomerCrud\Domain\Customer\CustomerNotFound;
use CustomerCrud\Infrastructure\Persistence\InMemoryCustomerRepository;
use CustomerCrud\Tests\Support\Customers;
use PHPUnit\Framework\TestCase;

final class CustomerUseCasesTest extends TestCase
{
    private InMemoryCustomerRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryCustomerRepository();
    }

    public function testRegistersAValidCustomer(): void
    {
        $customer = $this->register()(Customers::input());

        self::assertSame('900123456', $customer->nit->body());
        self::assertSame(45_000_000, $customer->creditLimit->pesos());
        self::assertSame($customer, $this->repository->get($customer->nit));
    }

    public function testReportsEveryInvalidFieldAtOnce(): void
    {
        try {
            $this->register()(new CustomerInput());
            self::fail('expected ValidationFailed');
        } catch (ValidationFailed $e) {
            self::assertSame(['nit', 'company_name', 'address', 'phone', 'city', 'credit_limit'], array_keys($e->errors));
        }

        self::assertSame([], $this->repository->search(''));
    }

    public function testRejectsADuplicateNitAsAFieldError(): void
    {
        $this->register()(Customers::input());

        try {
            $this->register()(Customers::input(['nit' => '900.123.456', 'company_name' => 'Otra Empresa S.A.']));
            self::fail('expected ValidationFailed');
        } catch (ValidationFailed $e) {
            self::assertSame(['nit' => 'ya existe un cliente con el NIT 900.123.456-8'], $e->errors);
        }
    }

    public function testUpdateCanChangeTheNitItself(): void
    {
        $this->register()(Customers::input());

        $updated = $this->update()('900123456', Customers::input(['nit' => '800197268-4']));

        self::assertSame('800197268', $updated->nit->body());
        self::assertCount(1, $this->repository->search(''));
        $this->expectException(CustomerNotFound::class);
        (new GetCustomer($this->repository))('900123456');
    }

    public function testUpdateRefusesANitOwnedByAnotherCustomer(): void
    {
        $this->register()(Customers::input());
        $this->register()(Customers::input(['nit' => '800197268', 'company_name' => 'Dirección de Impuestos']));

        try {
            $this->update()('900123456', Customers::input(['nit' => '800197268']));
            self::fail('expected ValidationFailed');
        } catch (ValidationFailed $e) {
            self::assertSame(['nit' => 'el NIT 800.197.268-4 ya pertenece a otro cliente'], $e->errors);
        }
    }

    public function testUpdateOfAMissingCustomerIsNotFound(): void
    {
        $this->expectException(CustomerNotFound::class);
        $this->update()('900123456', Customers::input());
    }

    public function testMalformedIdentifiersAreNotFound(): void
    {
        $this->expectException(CustomerNotFound::class);
        (new GetCustomer($this->repository))('abc');
    }

    public function testDeletesACustomer(): void
    {
        $this->register()(Customers::input());

        (new DeleteCustomer($this->repository))('900123456');

        self::assertSame([], $this->repository->search(''));
        $this->expectException(CustomerNotFound::class);
        (new DeleteCustomer($this->repository))('900123456');
    }

    public function testListsAndSearchesByNameCityOrNit(): void
    {
        $this->register()(Customers::input());
        $this->register()(Customers::input(['nit' => '830045678', 'company_name' => 'Ingeniería Caribe S.A.', 'city' => 'Barranquilla']));

        $list = new ListCustomers($this->repository);

        self::assertSame(['Andes Logística S.A.S.', 'Ingeniería Caribe S.A.'], array_map(
            static fn ($c): string => $c->companyName->value(),
            $list(),
        ));
        self::assertCount(1, $list('  barranquilla '));
        self::assertCount(1, $list('ANDES'));
        self::assertCount(1, $list('830045'));
        self::assertCount(0, $list('Cali'));
    }

    private function register(): RegisterCustomer
    {
        return new RegisterCustomer($this->repository, new CustomerAssembler());
    }

    private function update(): UpdateCustomer
    {
        return new UpdateCustomer($this->repository, new CustomerAssembler());
    }
}
