<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use CustomerCrud\Http\Session\Session;

/**
 * Synchronizer token pattern: one random token per session, compared in constant time.
 */
final class Csrf
{
    public const FIELD = '_token';

    private const SESSION_KEY = '_csrf';
    private const TOKEN_BYTES = 32;

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::SESSION_KEY);
        if (is_string($token) && $token !== '') {
            return $token;
        }

        $token = bin2hex(random_bytes(self::TOKEN_BYTES));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function isValid(string $submitted): bool
    {
        $token = $this->session->get(self::SESSION_KEY);

        return is_string($token) && $token !== '' && hash_equals($token, $submitted);
    }
}
