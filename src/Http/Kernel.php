<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use Closure;
use CustomerCrud\Domain\Customer\CustomerNotFound;

/**
 * Routes a request to the controller, enforces CSRF on every state-changing request and
 * stamps the security headers on every response.
 */
final class Kernel
{
    private const NIT = '(\d{6,15})';

    private const SECURITY_HEADERS = [
        'Content-Security-Policy' => "default-src 'none'; style-src https://cdn.jsdelivr.net; img-src 'self' data:;"
            ." form-action 'self'; frame-ancestors 'none'; base-uri 'none'",
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'same-origin',
    ];

    /** @var list<array{string, string, Closure(Request, string): Response}> */
    private readonly array $routes;

    public function __construct(
        CustomerController $customers,
        private readonly Csrf $csrf,
        private readonly View $view,
    ) {
        $this->routes = [
            ['GET', '#^/$#', static fn (Request $r): Response => $customers->index($r)],
            ['GET', '#^/customers/new$#', static fn (): Response => $customers->create()],
            ['POST', '#^/customers$#', static fn (Request $r): Response => $customers->store($r)],
            ['GET', '#^/customers/'.self::NIT.'$#', static fn (Request $r, string $nit): Response => $customers->show($nit)],
            ['GET', '#^/customers/'.self::NIT.'/edit$#', static fn (Request $r, string $nit): Response => $customers->edit($nit)],
            ['POST', '#^/customers/'.self::NIT.'$#', static fn (Request $r, string $nit): Response => $customers->update($r, $nit)],
            ['GET', '#^/customers/'.self::NIT.'/delete$#', static fn (Request $r, string $nit): Response => $customers->confirmDelete($nit)],
            ['POST', '#^/customers/'.self::NIT.'/delete$#', static fn (Request $r, string $nit): Response => $customers->destroy($nit)],
        ];
    }

    public function handle(Request $request): Response
    {
        return $this->dispatch($request)->withHeaders(self::SECURITY_HEADERS);
    }

    private function dispatch(Request $request): Response
    {
        $allowed = [];

        foreach ($this->routes as [$method, $pattern, $handler]) {
            if (preg_match($pattern, $request->path, $matches) !== 1) {
                continue;
            }

            if ($method !== $request->method) {
                $allowed[] = $method;

                continue;
            }

            if ($method === 'POST' && !$this->csrf->isValid($request->input(Csrf::FIELD))) {
                return $this->error(403, 'La sesión del formulario expiró. Vuelva a cargar la página e intente de nuevo.');
            }

            try {
                return $handler($request, $matches[1] ?? '');
            } catch (CustomerNotFound) {
                return $this->error(404, 'El cliente no existe o ya fue eliminado.');
            }
        }

        if ($allowed !== []) {
            return $this->error(405, 'Método no permitido.')->withHeaders(['Allow' => implode(', ', $allowed)]);
        }

        return $this->error(404, 'La página no existe.');
    }

    private function error(int $status, string $message): Response
    {
        return Response::html($this->view->render('error', 'Error '.$status, [
            'status' => $status,
            'message' => $message,
        ]), $status);
    }
}
