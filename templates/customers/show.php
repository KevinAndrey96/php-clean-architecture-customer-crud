<?php
/**
 * @var CustomerCrud\Http\View                $view
 * @var CustomerCrud\Domain\Customer\Customer $customer
 */

$path = '/customers/'.$customer->nit->body();
?>
<h1 class="h3 mb-3"><?= $view->e($customer->companyName->value()) ?></h1>

<div class="card card-body bg-white mb-3">
    <dl class="row mb-0">
        <dt class="col-sm-4">NIT</dt>
        <dd class="col-sm-8"><?= $view->e($customer->nit->formatted()) ?></dd>
        <dt class="col-sm-4">Dirección</dt>
        <dd class="col-sm-8"><?= $view->e($customer->address->value()) ?></dd>
        <dt class="col-sm-4">Ciudad</dt>
        <dd class="col-sm-8"><?= $view->e($customer->city->value()) ?></dd>
        <dt class="col-sm-4">Teléfono</dt>
        <dd class="col-sm-8"><?= $view->e($customer->phone->formatted()) ?></dd>
        <dt class="col-sm-4">Crédito aprobado</dt>
        <dd class="col-sm-8 mb-0"><?= $view->e($customer->creditLimit->formatted()) ?></dd>
    </dl>
</div>

<div class="d-flex gap-2">
    <a class="btn btn-success" href="<?= $view->e($path) ?>/edit">Editar</a>
    <a class="btn btn-outline-danger" href="<?= $view->e($path) ?>/delete">Eliminar</a>
    <a class="btn btn-outline-secondary" href="/">Volver al listado</a>
</div>
