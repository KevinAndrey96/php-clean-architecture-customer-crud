<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

/**
 * Colombian phone number under the 2021 national numbering plan: ten digits, mobile
 * numbers start with 3 and landlines with 60 followed by the department indicator.
 */
final class PhoneNumber
{
    public const FIELD = 'phone';

    private const COUNTRY_CODE = '57';

    private function __construct(private readonly string $digits)
    {
    }

    /**
     * Accepts spaces, dashes, parentheses and an optional +57 prefix.
     *
     * @throws InvalidCustomerField
     */
    public static function fromString(string $raw): self
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            throw new InvalidCustomerField(self::FIELD, 'digite el teléfono');
        }

        if (preg_match('/^\+?[\d\s()\-]+$/', $trimmed) !== 1) {
            throw new InvalidCustomerField(self::FIELD, 'el teléfono solo admite dígitos, espacios, guiones y el prefijo +57');
        }

        $digits = (string) preg_replace('/\D/', '', $trimmed);
        if (strlen($digits) === 12 && str_starts_with($digits, self::COUNTRY_CODE)) {
            $digits = substr($digits, strlen(self::COUNTRY_CODE));
        }

        if (preg_match('/^(3\d{9}|60\d{8})$/', $digits) !== 1) {
            throw new InvalidCustomerField(
                self::FIELD,
                'el teléfono debe tener 10 dígitos: celular 3XX XXX XXXX o fijo 60X XXX XXXX',
            );
        }

        return new self($digits);
    }

    public function digits(): string
    {
        return $this->digits;
    }

    public function formatted(): string
    {
        return sprintf('%s %s %s', substr($this->digits, 0, 3), substr($this->digits, 3, 3), substr($this->digits, 6));
    }
}
