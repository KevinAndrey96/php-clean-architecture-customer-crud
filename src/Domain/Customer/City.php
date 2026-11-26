<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

final class City extends BoundedText
{
    public const FIELD = 'city';

    protected static function bounds(): array
    {
        return [self::FIELD, 'la ciudad', 2, 80];
    }
}
