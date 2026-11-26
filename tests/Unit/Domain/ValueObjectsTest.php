<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Unit\Domain;

use CustomerCrud\Domain\Customer\Address;
use CustomerCrud\Domain\Customer\City;
use CustomerCrud\Domain\Customer\CompanyName;
use CustomerCrud\Domain\Customer\CreditLimit;
use CustomerCrud\Domain\Customer\InvalidCustomerField;
use CustomerCrud\Domain\Customer\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function testBoundedTextCollapsesWhitespace(): void
    {
        self::assertSame('Andes Logística S.A.S.', CompanyName::fromString("  Andes \t Logística\n S.A.S. ")->value());
    }

    public function testBoundedTextCountsCharactersNotBytes(): void
    {
        self::assertSame(str_repeat('ñ', 80), City::fromString(str_repeat('ñ', 80))->value());

        $this->expectException(InvalidCustomerField::class);
        City::fromString(str_repeat('ñ', 81));
    }

    /**
     * @return iterable<string, array{callable(): object, string, string}>
     */
    public static function invalidTexts(): iterable
    {
        yield 'blank company name' => [static fn (): object => CompanyName::fromString('   '), CompanyName::FIELD, 'digite la razón social'];
        yield 'short address' => [static fn (): object => Address::fromString('Cl 1'), Address::FIELD, 'entre 5 y 160'];
        yield 'one letter city' => [static fn (): object => City::fromString('B'), City::FIELD, 'entre 2 y 80'];
    }

    /**
     * @param callable(): object $build
     */
    #[DataProvider('invalidTexts')]
    public function testBoundedTextReportsItsField(callable $build, string $field, string $message): void
    {
        try {
            $build();
            self::fail('expected InvalidCustomerField');
        } catch (InvalidCustomerField $e) {
            self::assertSame($field, $e->field);
            self::assertStringContainsString($message, $e->getMessage());
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function validPhones(): iterable
    {
        yield 'mobile' => ['310 456 7890', '3104567890'];
        yield 'Bogotá landline' => ['(601) 745-6321', '6017456321'];
        yield 'with country code' => ['+57 310 456 7890', '3104567890'];
    }

    #[DataProvider('validPhones')]
    public function testPhoneNormalizesToTenDigits(string $raw, string $digits): void
    {
        $phone = PhoneNumber::fromString($raw);

        self::assertSame($digits, $phone->digits());
        self::assertSame(substr($digits, 0, 3).' '.substr($digits, 3, 3).' '.substr($digits, 6), $phone->formatted());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidPhones(): iterable
    {
        yield 'empty' => [''];
        yield 'pre-2021 seven digit landline' => ['7456321'];
        yield 'letters' => ['310-ABC-7890'];
        yield 'unknown prefix' => ['5104567890'];
        yield 'eleven digits' => ['31045678901'];
    }

    #[DataProvider('invalidPhones')]
    public function testPhoneRejects(string $raw): void
    {
        $this->expectException(InvalidCustomerField::class);
        PhoneNumber::fromString($raw);
    }

    /**
     * @return iterable<string, array{string, int, string}>
     */
    public static function validCredits(): iterable
    {
        yield 'plain' => ['15000000', 15_000_000, '$ 15.000.000'];
        yield 'thousands separators' => ['15.000.000', 15_000_000, '$ 15.000.000'];
        yield 'currency sign' => ['$ 2.500.000', 2_500_000, '$ 2.500.000'];
        yield 'no credit' => ['0', 0, '$ 0'];
    }

    #[DataProvider('validCredits')]
    public function testCreditParsesColombianNotation(string $raw, int $pesos, string $formatted): void
    {
        $credit = CreditLimit::fromString($raw);

        self::assertSame($pesos, $credit->pesos());
        self::assertSame($formatted, $credit->formatted());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidCredits(): iterable
    {
        yield 'empty' => [''];
        yield 'negative' => ['-1000'];
        yield 'decimals' => ['1500,50'];
        yield 'above one trillion' => ['1000000000001'];
        yield 'integer overflow' => ['99999999999999999999999'];
    }

    #[DataProvider('invalidCredits')]
    public function testCreditRejects(string $raw): void
    {
        $this->expectException(InvalidCustomerField::class);
        CreditLimit::fromString($raw);
    }

    public function testCreditFromPesosGuardsTheRange(): void
    {
        self::assertSame(1_000_000_000_000, CreditLimit::fromPesos(1_000_000_000_000)->pesos());

        $this->expectException(InvalidCustomerField::class);
        CreditLimit::fromPesos(-1);
    }
}
