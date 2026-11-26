<?php

declare(strict_types=1);

namespace CustomerCrud\Http\Session;

use RuntimeException;

/**
 * Thin adapter over $_SESSION; the front controller starts the session with hardened
 * cookie settings before building the kernel.
 */
final class NativeSession implements Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('native session: session is not started');
        }
    }

    public function get(string $key): mixed
    {
        return $_SESSION[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }
}
