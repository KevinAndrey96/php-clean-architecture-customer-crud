<?php

declare(strict_types=1);

namespace CustomerCrud\Infrastructure\Persistence;

use PDO;
use RuntimeException;

/**
 * Opens the PDO connection from DB_DSN, DB_USER and DB_PASSWORD. Credentials never live in
 * the source tree.
 */
final class ConnectionFactory
{
    /**
     * @param array<string, string|false> $environment typically getenv()
     */
    public static function fromEnvironment(array $environment): PDO
    {
        $dsn = $environment['DB_DSN'] ?? false;
        if ($dsn === false || $dsn === '') {
            throw new RuntimeException('database connection: DB_DSN is not set');
        }

        $user = $environment['DB_USER'] ?? false;
        $password = $environment['DB_PASSWORD'] ?? false;

        return new PDO(
            $dsn,
            $user === false ? null : $user,
            $password === false ? null : $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        );
    }
}
