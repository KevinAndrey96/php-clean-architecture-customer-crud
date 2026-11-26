<?php

declare(strict_types=1);

namespace CustomerCrud\Http\Session;

interface Session
{
    public function get(string $key): mixed;

    public function set(string $key, mixed $value): void;

    public function remove(string $key): void;
}
