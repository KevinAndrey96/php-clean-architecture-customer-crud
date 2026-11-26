<?php

declare(strict_types=1);

namespace CustomerCrud\Http;

use CustomerCrud\Application\Customer\CustomerAssembler;
use CustomerCrud\Application\Customer\DeleteCustomer;
use CustomerCrud\Application\Customer\GetCustomer;
use CustomerCrud\Application\Customer\ListCustomers;
use CustomerCrud\Application\Customer\RegisterCustomer;
use CustomerCrud\Application\Customer\UpdateCustomer;
use CustomerCrud\Domain\Customer\CustomerRepository;
use CustomerCrud\Http\Session\Session;

/**
 * Composition root: the only place that knows every concrete class.
 */
final class AppFactory
{
    public static function create(CustomerRepository $customers, Session $session, string $templateDir): Kernel
    {
        $csrf = new Csrf($session);
        $flash = new Flash($session);
        $view = new View($templateDir, $csrf, $flash);
        $assembler = new CustomerAssembler();

        $controller = new CustomerController(
            new ListCustomers($customers),
            new GetCustomer($customers),
            new RegisterCustomer($customers, $assembler),
            new UpdateCustomer($customers, $assembler),
            new DeleteCustomer($customers),
            $view,
            $flash,
        );

        return new Kernel($controller, $csrf, $view);
    }
}
