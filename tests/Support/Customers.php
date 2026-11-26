<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Support;

use CustomerCrud\Application\Customer\CustomerAssembler;
use CustomerCrud\Application\Customer\CustomerInput;
use CustomerCrud\Domain\Customer\Customer;

/**
 * Valid fixtures; check digits follow the DIAN modulo 11 algorithm.
 */
final class Customers
{
    /**
     * @param array<string, string> $overrides keyed by form field name
     *
     * @return array<string, string>
     */
    public static function form(array $overrides = []): array
    {
        return $overrides + [
            'nit' => '900123456-8',
            'company_name' => 'Andes Logística S.A.S.',
            'address' => 'Calle 26 # 68C-61',
            'phone' => '601 745 6321',
            'city' => 'Bogotá',
            'credit_limit' => '45.000.000',
        ];
    }

    /**
     * @param array<string, string> $overrides keyed by form field name
     */
    public static function input(array $overrides = []): CustomerInput
    {
        return CustomerInput::fromArray(self::form($overrides));
    }

    /**
     * @param array<string, string> $overrides keyed by form field name
     */
    public static function customer(array $overrides = []): Customer
    {
        return (new CustomerAssembler())->assemble(self::input($overrides));
    }
}
