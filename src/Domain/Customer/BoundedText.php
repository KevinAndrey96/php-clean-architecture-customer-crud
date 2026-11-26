<?php

declare(strict_types=1);

namespace CustomerCrud\Domain\Customer;

use Stringable;

/**
 * Free text with collapsed whitespace and a length range counted in characters, not bytes.
 */
abstract class BoundedText implements Stringable
{
    final private function __construct(private readonly string $value)
    {
    }

    /**
     * @throws InvalidCustomerField
     */
    final public static function fromString(string $raw): static
    {
        [$field, $label, $min, $max] = static::bounds();

        $value = trim((string) preg_replace('/\s+/u', ' ', $raw));
        $length = mb_strlen($value);

        if ($length === 0) {
            throw new InvalidCustomerField($field, sprintf('digite %s', $label));
        }

        if ($length < $min || $length > $max) {
            throw new InvalidCustomerField($field, sprintf('%s debe tener entre %d y %d caracteres', $label, $min, $max));
        }

        return new static($value);
    }

    final public function value(): string
    {
        return $this->value;
    }

    final public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return array{string, string, int, int} form field, label for messages, min and max length
     */
    abstract protected static function bounds(): array;
}
