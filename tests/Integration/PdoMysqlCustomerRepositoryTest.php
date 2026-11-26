<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Integration;

use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Infrastructure\Persistence\ConnectionFactory;
use CustomerCrud\Infrastructure\Persistence\PdoCustomerRepository;
use PDO;

/**
 * Runs the contract against a real MySQL server when TEST_MYSQL_DSN is set (CI does);
 * skipped otherwise so the default suite needs nothing but pdo_sqlite.
 */
final class PdoMysqlCustomerRepositoryTest extends CustomerRepositoryContract
{
    private static ?PDO $pdo = null;

    protected function setUp(): void
    {
        $dsn = getenv('TEST_MYSQL_DSN');
        if ($dsn === false || $dsn === '') {
            self::markTestSkipped('TEST_MYSQL_DSN is not set');
        }

        if (self::$pdo === null) {
            self::$pdo = ConnectionFactory::fromEnvironment([
                'DB_DSN' => $dsn,
                'DB_USER' => getenv('TEST_MYSQL_USER'),
                'DB_PASSWORD' => getenv('TEST_MYSQL_PASSWORD'),
            ]);
        }

        self::$pdo->exec('DROP TABLE IF EXISTS customers');
        self::$pdo->exec(Schema::sql());
    }

    protected function repository(): CustomerRepository
    {
        self::assertNotNull(self::$pdo);

        return new PdoCustomerRepository(self::$pdo);
    }
}
