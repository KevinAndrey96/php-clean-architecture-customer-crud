<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use CustomerCrud\Application\Customer\CustomerInput;
use CustomerCrud\Application\Customer\DeleteCustomer;
use CustomerCrud\Application\Customer\GetCustomer;
use CustomerCrud\Application\Customer\ListCustomers;
use CustomerCrud\Application\Customer\RegisterCustomer;
use CustomerCrud\Application\Customer\UpdateCustomer;
use CustomerCrud\Application\Customer\ValidationFailed;
use CustomerCrud\Domain\Customer\Customer;

final class CustomerController
{
    private const STATUS_UNPROCESSABLE = 422;

    public function __construct(
        private readonly ListCustomers $listCustomers,
        private readonly GetCustomer $getCustomer,
        private readonly RegisterCustomer $registerCustomer,
        private readonly UpdateCustomer $updateCustomer,
        private readonly DeleteCustomer $deleteCustomer,
        private readonly View $view,
        private readonly Flash $flash,
    ) {
    }

    public function index(Request $request): Response
    {
        $term = $request->query('q');

        return Response::html($this->view->render('customers/index', 'Clientes', [
            'customers' => ($this->listCustomers)($term),
            'term' => $term,
        ]));
    }

    public function create(): Response
    {
        return $this->form('Nuevo cliente', '/customers', new CustomerInput());
    }

    public function store(Request $request): Response
    {
        $input = CustomerInput::fromArray($request->body);

        try {
            $customer = ($this->registerCustomer)($input);
        } catch (ValidationFailed $e) {
            return $this->form('Nuevo cliente', '/customers', $input, $e->errors, self::STATUS_UNPROCESSABLE);
        }

        $this->flash->set(sprintf('Cliente %s registrado', $customer->companyName));

        return Response::redirect(self::customerPath($customer));
    }

    public function show(string $nit): Response
    {
        $customer = ($this->getCustomer)($nit);

        return Response::html($this->view->render('customers/show', $customer->companyName->value(), [
            'customer' => $customer,
        ]));
    }

    public function edit(string $nit): Response
    {
        $customer = ($this->getCustomer)($nit);

        return $this->form('Editar cliente', self::customerPath($customer), CustomerInput::fromCustomer($customer));
    }

    public function update(Request $request, string $nit): Response
    {
        $input = CustomerInput::fromArray($request->body);

        try {
            $customer = ($this->updateCustomer)($nit, $input);
        } catch (ValidationFailed $e) {
            return $this->form('Editar cliente', '/customers/'.$nit, $input, $e->errors, self::STATUS_UNPROCESSABLE);
        }

        $this->flash->set(sprintf('Cliente %s actualizado', $customer->companyName));

        return Response::redirect(self::customerPath($customer));
    }

    public function confirmDelete(string $nit): Response
    {
        $customer = ($this->getCustomer)($nit);

        return Response::html($this->view->render('customers/delete', 'Eliminar cliente', [
            'customer' => $customer,
        ]));
    }

    public function destroy(string $nit): Response
    {
        $customer = ($this->getCustomer)($nit);
        ($this->deleteCustomer)($nit);

        $this->flash->set(sprintf('Cliente %s eliminado', $customer->companyName));

        return Response::redirect('/');
    }

    private static function customerPath(Customer $customer): string
    {
        return '/customers/'.$customer->nit->body();
    }

    /**
     * @param array<string, string> $errors
     */
    private function form(string $title, string $action, CustomerInput $input, array $errors = [], int $status = 200): Response
    {
        return Response::html($this->view->render('customers/form', $title, [
            'title' => $title,
            'action' => $action,
            'input' => $input,
            'errors' => $errors,
        ]), $status);
    }
}
