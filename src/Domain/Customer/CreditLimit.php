<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

/**
 * Approved credit in whole Colombian pesos (COP has no cents in commercial practice).
 */
final class CreditLimit
{
    public const FIELD = 'credit_limit';

    /** One trillion pesos ("un billón"), far above any credit line and safe for a signed BIGINT. */
    private const MAX_PESOS = 1_000_000_000_000;

    private function __construct(private readonly int $pesos)
    {
    }

    /**
     * Accepts "15000000", "15.000.000" and "$ 15.000.000". Zero means no credit.
     *
     * @throws InvalidCustomerField
     */
    public static function fromString(string $raw): self
    {
        $value = str_replace(['$', '.', ' '], '', trim($raw));
        if ($value === '') {
            throw new InvalidCustomerField(self::FIELD, 'digite el crédito aprobado');
        }

        if (preg_match('/^\d+$/', $value) !== 1) {
            throw new InvalidCustomerField(self::FIELD, 'el crédito debe ser un valor entero en pesos, sin decimales');
        }

        if (strlen(ltrim($value, '0')) > strlen((string) self::MAX_PESOS) || (int) $value > self::MAX_PESOS) {
            throw new InvalidCustomerField(self::FIELD, 'el crédito supera el máximo permitido');
        }

        return new self((int) $value);
    }

    /**
     * @throws InvalidCustomerField
     */
    public static function fromPesos(int $pesos): self
    {
        if ($pesos < 0 || $pesos > self::MAX_PESOS) {
            throw new InvalidCustomerField(self::FIELD, 'el crédito está fuera del rango permitido');
        }

        return new self($pesos);
    }

    public function pesos(): int
    {
        return $this->pesos;
    }

    public function formatted(): string
    {
        return '$ '.number_format($this->pesos, 0, ',', '.');
    }
}
