<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

final class Address extends BoundedText
{
    public const FIELD = 'address';

    protected static function bounds(): array
    {
        return [self::FIELD, 'la dirección', 5, 160];
    }
}
