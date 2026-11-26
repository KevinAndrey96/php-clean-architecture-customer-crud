<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

final class CompanyName extends BoundedText
{
    public const FIELD = 'company_name';

    protected static function bounds(): array
    {
        return [self::FIELD, 'la razón social', 2, 120];
    }
}
