<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use CustomerCrud\Http\Session\Session;

/**
 * One-shot message that survives the redirect after a successful POST.
 */
final class Flash
{
    private const SESSION_KEY = '_flash';

    public function __construct(private readonly Session $session)
    {
    }

    public function set(string $message): void
    {
        $this->session->set(self::SESSION_KEY, $message);
    }

    public function pull(): ?string
    {
        $message = $this->session->get(self::SESSION_KEY);
        $this->session->remove(self::SESSION_KEY);

        return is_string($message) ? $message : null;
    }
}
