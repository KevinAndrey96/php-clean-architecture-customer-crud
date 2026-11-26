<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

final class Request
{
    /**
     * @param array<array-key, mixed> $query
     * @param array<array-key, mixed> $body
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        public readonly array $query = [],
        public readonly array $body = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url(is_string($uri) ? $uri : '/', PHP_URL_PATH);

        return new self(
            strtoupper(is_string($method) ? $method : 'GET'),
            self::normalizePath(is_string($path) ? $path : '/'),
            $_GET,
            $_POST,
        );
    }

    public static function normalizePath(string $path): string
    {
        $trimmed = rtrim($path, '/');

        return $trimmed === '' ? '/' : $trimmed;
    }

    public function query(string $key): string
    {
        $value = $this->query[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    public function input(string $key): string
    {
        $value = $this->body[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
