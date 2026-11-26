<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Integration;

use RuntimeException;

final class Schema
{
    public static function sql(): string
    {
        return self::read('schema.sql');
    }

    public static function seed(): string
    {
        return self::read('seed.sql');
    }

    private static function read(string $file): string
    {
        $sql = file_get_contents(dirname(__DIR__, 2).'/database/'.$file);
        if ($sql === false) {
            throw new RuntimeException(sprintf('schema: %s is not readable', $file));
        }

        return $sql;
    }
}
