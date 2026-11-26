<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

/**
 * Colombian tax identification number (NIT) together with its DIAN check digit.
 *
 * The check digit is the modulo 11 checksum published by the DIAN: each digit of the
 * body, from right to left, is multiplied by 3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47,
 * 53, 59, 67 and 71; a remainder of 0 or 1 is the digit itself, otherwise 11 - remainder.
 */
final class Nit
{
    public const FIELD = 'nit';

    private const WEIGHTS = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
    private const MIN_BODY_LENGTH = 6;

    private function __construct(
        private readonly string $body,
        private readonly int $checkDigit,
    ) {
    }

    /**
     * Accepts "860007386", "860007386-1" and "860.007.386-1". When the check digit is
     * present it must match the computed one.
     *
     * @throws InvalidCustomerField
     */
    public static function fromString(string $raw): self
    {
        $value = str_replace(['.', ' '], '', trim($raw));
        if ($value === '') {
            throw new InvalidCustomerField(self::FIELD, 'digite el NIT');
        }

        if (preg_match('/^(\d+)(?:-(\d))?$/', $value, $matches) !== 1) {
            throw new InvalidCustomerField(
                self::FIELD,
                'el NIT solo admite dígitos, puntos y un guion antes del dígito de verificación',
            );
        }

        $body = $matches[1];
        self::assertBodyLength($body);

        $checkDigit = self::checkDigitOf($body);
        $given = $matches[2] ?? '';
        if ($given !== '' && (int) $given !== $checkDigit) {
            throw new InvalidCustomerField(
                self::FIELD,
                sprintf('el dígito de verificación no corresponde: para %s es %d', $body, $checkDigit),
            );
        }

        return new self($body, $checkDigit);
    }

    /**
     * @throws InvalidCustomerField
     */
    public static function checkDigitOf(string $body): int
    {
        if (preg_match('/^\d+$/', $body) !== 1) {
            throw new InvalidCustomerField(self::FIELD, 'el NIT solo admite dígitos');
        }

        self::assertBodyLength($body);

        $sum = 0;
        foreach (str_split(strrev($body)) as $position => $digit) {
            $sum += (int) $digit * self::WEIGHTS[$position];
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? $remainder : 11 - $remainder;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function checkDigit(): int
    {
        return $this->checkDigit;
    }

    /**
     * Human format used by the DIAN on the RUT, for example 860.007.386-1.
     */
    public function formatted(): string
    {
        $groups = str_split(strrev($this->body), 3);

        return strrev(implode('.', $groups)).'-'.$this->checkDigit;
    }

    public function equals(self $other): bool
    {
        return $this->body === $other->body;
    }

    /**
     * @throws InvalidCustomerField
     */
    private static function assertBodyLength(string $body): void
    {
        $length = strlen($body);
        if ($length < self::MIN_BODY_LENGTH || $length > count(self::WEIGHTS)) {
            throw new InvalidCustomerField(
                self::FIELD,
                sprintf('el NIT debe tener entre %d y %d dígitos sin el de verificación', self::MIN_BODY_LENGTH, count(self::WEIGHTS)),
            );
        }
    }
}
