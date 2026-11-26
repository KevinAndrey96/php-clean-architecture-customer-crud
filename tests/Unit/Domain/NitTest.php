<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Unit\Domain;

use CustomerCrud\Domain\Customer\InvalidCustomerField;
use CustomerCrud\Domain\Customer\Nit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class NitTest extends TestCase
{
    /**
     * Public NITs whose check digits are printed on their RUT.
     *
     * @return iterable<string, array{string, int}>
     */
    public static function publicNits(): iterable
    {
        yield 'DIAN' => ['800197268', 4];
        yield 'Bancolombia' => ['890903938', 8];
        yield 'Ecopetrol' => ['899999068', 1];
        yield 'remainder 0 or 1 keeps the remainder' => ['860007386', 1];
    }

    #[DataProvider('publicNits')]
    public function testComputesTheDianCheckDigit(string $body, int $checkDigit): void
    {
        self::assertSame($checkDigit, Nit::checkDigitOf($body));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function acceptedFormats(): iterable
    {
        yield 'body only' => ['800197268'];
        yield 'with check digit' => ['800197268-4'];
        yield 'RUT format' => ['800.197.268-4'];
        yield 'surrounding spaces' => ['  800 197 268-4 '];
    }

    #[DataProvider('acceptedFormats')]
    public function testParsesEveryCommonFormat(string $raw): void
    {
        $nit = Nit::fromString($raw);

        self::assertSame('800197268', $nit->body());
        self::assertSame(4, $nit->checkDigit());
        self::assertSame('800.197.268-4', $nit->formatted());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rejectedInputs(): iterable
    {
        yield 'empty' => ['', 'digite el NIT'];
        yield 'letters' => ['80019A268', 'solo admite dígitos'];
        yield 'two dashes' => ['800-197-268', 'solo admite dígitos'];
        yield 'wrong check digit' => ['800197268-5', 'para 800197268 es 4'];
        yield 'too short' => ['12345', 'entre 6 y 15'];
        yield 'too long' => ['1234567890123456', 'entre 6 y 15'];
    }

    #[DataProvider('rejectedInputs')]
    public function testRejectsInvalidInput(string $raw, string $message): void
    {
        try {
            Nit::fromString($raw);
            self::fail('expected InvalidCustomerField');
        } catch (InvalidCustomerField $e) {
            self::assertSame(Nit::FIELD, $e->field);
            self::assertStringContainsString($message, $e->getMessage());
        }
    }

    public function testFormatsShortBodiesWithoutALeadingSeparator(): void
    {
        self::assertSame('123.456-'.Nit::checkDigitOf('123456'), Nit::fromString('123456')->formatted());
    }

    public function testEqualityIgnoresFormatting(): void
    {
        self::assertTrue(Nit::fromString('800.197.268-4')->equals(Nit::fromString('800197268')));
        self::assertFalse(Nit::fromString('800197268')->equals(Nit::fromString('890903938')));
    }
}
