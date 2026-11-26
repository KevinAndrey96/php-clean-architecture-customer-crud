<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Integration;

use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Infrastructure\Persistence\ConnectionFactory;
use CustomerCrud\Infrastructure\Persistence\PdoCustomerRepository;

final class PdoSqliteCustomerRepositoryTest extends CustomerRepositoryContract
{
    protected function repository(): CustomerRepository
    {
        $pdo = ConnectionFactory::fromEnvironment(['DB_DSN' => 'sqlite::memory:']);
        $pdo->exec(Schema::sql());

        return new PdoCustomerRepository($pdo);
    }

    public function testSeedFileLoadsAgainstTheSchema(): void
    {
        $pdo = ConnectionFactory::fromEnvironment(['DB_DSN' => 'sqlite::memory:']);
        $pdo->exec(Schema::sql());
        $pdo->exec(Schema::seed());

        self::assertCount(4, (new PdoCustomerRepository($pdo))->search(''));
    }
}
