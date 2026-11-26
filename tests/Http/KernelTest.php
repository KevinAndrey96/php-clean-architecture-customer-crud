<?php

declare(strict_types=1);

namespace CustomerCrud\Tests\Http;

use CustomerCrud\Domain\Customer\Nit;
use CustomerCrud\Http\AppFactory;
use CustomerCrud\Http\Csrf;
use CustomerCrud\Http\Kernel;
use CustomerCrud\Http\Request;
use CustomerCrud\Http\Response;
use CustomerCrud\Http\Session\ArraySession;
use CustomerCrud\Infrastructure\Persistence\InMemoryCustomerRepository;
use CustomerCrud\Tests\Support\Customers;
use PHPUnit\Framework\TestCase;

final class KernelTest extends TestCase
{
    private InMemoryCustomerRepository $repository;
    private ArraySession $session;
    private Kernel $kernel;

    protected function setUp(): void
    {
        $this->repository = new InMemoryCustomerRepository();
        $this->session = new ArraySession();
        $this->kernel = AppFactory::create($this->repository, $this->session, dirname(__DIR__, 2).'/templates');
    }

    public function testEmptyListInvitesToRegister(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('Aún no hay clientes registrados', $response->body);
    }

    public function testEveryResponseCarriesTheSecurityHeaders(): void
    {
        foreach ([$this->get('/'), $this->get('/missing'), $this->post('/customers', Customers::form(), withToken: false)] as $response) {
            self::assertStringContainsString("frame-ancestors 'none'", (string) $response->header('Content-Security-Policy'));
            self::assertSame('nosniff', $response->header('X-Content-Type-Options'));
            self::assertSame('DENY', $response->header('X-Frame-Options'));
        }
    }

    public function testRegistersAndRedirectsWithSeeOther(): void
    {
        $response = $this->post('/customers', Customers::form());

        self::assertSame(303, $response->status);
        self::assertSame('/customers/900123456', $response->header('Location'));

        $show = $this->get('/customers/900123456');
        self::assertSame(200, $show->status);
        self::assertStringContainsString('Cliente Andes Logística S.A.S. registrado', $show->body);
        self::assertStringContainsString('900.123.456-8', $show->body);
        self::assertStringContainsString('$ 45.000.000', $show->body);

        self::assertStringNotContainsString('registrado', $this->get('/')->body, 'the flash message is shown once');
    }

    public function testInvalidSubmissionKeepsTheTypedValuesAndFlagsEveryField(): void
    {
        $response = $this->post('/customers', Customers::form(['nit' => '900123456-1', 'phone' => '123']));

        self::assertSame(422, $response->status);
        self::assertStringContainsString('para 900123456 es 8', $response->body);
        self::assertStringContainsString('el teléfono debe tener 10 dígitos', $response->body);
        self::assertStringContainsString('value="900123456-1"', $response->body);
        self::assertSame(2, substr_count($response->body, 'is-invalid'));
        self::assertSame([], $this->repository->search(''));
    }

    public function testEscapesUserContent(): void
    {
        $this->post('/customers', Customers::form(['company_name' => '<script>alert(1)</script>']));

        $body = $this->get('/')->body;

        self::assertStringNotContainsString('<script>alert(1)</script>', $body);
        self::assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $body);
    }

    public function testRejectsPostsWithoutAValidCsrfToken(): void
    {
        self::assertSame(403, $this->post('/customers', Customers::form(), withToken: false)->status);
        self::assertSame(403, $this->post('/customers', Customers::form() + [Csrf::FIELD => 'forged'], withToken: false)->status);
        self::assertSame([], $this->repository->search(''));
    }

    public function testEditFormIsPrefilledAndUpdateCanChangeTheNit(): void
    {
        $this->post('/customers', Customers::form());

        $edit = $this->get('/customers/900123456/edit');
        self::assertStringContainsString('value="900.123.456-8"', $edit->body);
        self::assertStringContainsString('action="/customers/900123456"', $edit->body);

        $response = $this->post('/customers/900123456', Customers::form(['nit' => '800197268-4']));

        self::assertSame(303, $response->status);
        self::assertSame('/customers/800197268', $response->header('Location'));
        self::assertSame(404, $this->get('/customers/900123456')->status);
        self::assertSame('800197268', $this->repository->get(Nit::fromString('800197268'))->nit->body());
    }

    public function testDeleteNeedsAConfirmedPost(): void
    {
        $this->post('/customers', Customers::form());

        $confirm = $this->get('/customers/900123456/delete');
        self::assertSame(200, $confirm->status);
        self::assertStringContainsString('Esta acción no se puede deshacer', $confirm->body);
        self::assertCount(1, $this->repository->search(''));

        $response = $this->post('/customers/900123456/delete', []);

        self::assertSame(303, $response->status);
        self::assertSame('/', $response->header('Location'));
        self::assertStringContainsString('Cliente Andes Logística S.A.S. eliminado', $this->get('/')->body);
        self::assertSame([], $this->repository->search(''));
    }

    public function testSearchFiltersTheList(): void
    {
        $this->post('/customers', Customers::form());
        $this->post('/customers', Customers::form(['nit' => '830045678', 'company_name' => 'Ingeniería Caribe S.A.', 'city' => 'Barranquilla']));

        $body = $this->get('/', ['q' => 'barranquilla'])->body;

        self::assertStringContainsString('Ingeniería Caribe S.A.', $body);
        self::assertStringNotContainsString('Andes Logística', $body);
        self::assertStringContainsString('Ningún cliente coincide', $this->get('/', ['q' => 'Cali'])->body);
    }

    public function testUnknownCustomersAndPagesAre404(): void
    {
        self::assertSame(404, $this->get('/customers/900123456')->status);
        self::assertSame(404, $this->get('/customers/900123456/edit')->status);
        self::assertSame(404, $this->post('/customers/900123456/delete', [])->status);
        self::assertSame(404, $this->get('/Vista/index.php')->status);
    }

    public function testWrongMethodIs405WithAllowHeader(): void
    {
        $response = $this->kernel->handle(new Request('DELETE', '/customers/900123456'));

        self::assertSame(405, $response->status);
        self::assertSame('GET, POST', $response->header('Allow'));
    }

    public function testNormalizesTrailingSlashes(): void
    {
        self::assertSame('/customers/new', Request::normalizePath('/customers/new/'));
        self::assertSame('/', Request::normalizePath(''));
    }

    /**
     * @param array<string, string> $query
     */
    private function get(string $path, array $query = []): Response
    {
        return $this->kernel->handle(new Request('GET', $path, $query));
    }

    /**
     * @param array<string, string> $form
     */
    private function post(string $path, array $form, bool $withToken = true): Response
    {
        if ($withToken) {
            $form[Csrf::FIELD] = (new Csrf($this->session))->token();
        }

        return $this->kernel->handle(new Request('POST', $path, [], $form));
    }
}
