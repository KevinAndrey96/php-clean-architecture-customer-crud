<?php

declare(strict_types=1);

use CustomerCrud\Http\AppFactory;
use CustomerCrud\Http\Request;
use CustomerCrud\Http\Response;
use CustomerCrud\Http\Session\NativeSession;
use CustomerCrud\Infrastructure\Persistence\ConnectionFactory;
use CustomerCrud\Infrastructure\Persistence\PdoCustomerRepository;

require dirname(__DIR__).'/vendor/autoload.php';

session_start([
    'name' => 'customer_crud',
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => ($_SERVER['HTTPS'] ?? 'off') !== 'off',
    'use_strict_mode' => true,
    'use_only_cookies' => true,
]);

try {
    $kernel = AppFactory::create(
        new PdoCustomerRepository(ConnectionFactory::fromEnvironment(getenv())),
        new NativeSession(),
        dirname(__DIR__).'/templates',
    );

    $kernel->handle(Request::fromGlobals())->send();
} catch (Throwable $e) {
    error_log('customer crud: '.$e->getMessage());
    (new Response(500, 'Error interno del servidor', ['Content-Type' => 'text/plain; charset=utf-8']))->send();
}
