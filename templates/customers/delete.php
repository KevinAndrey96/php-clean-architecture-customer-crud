<?php
/**
 * @var CustomerCrud\Http\View                $view
 * @var CustomerCrud\Domain\Customer\Customer $customer
 */

$path = '/customers/'.$customer->nit->body();
?>
<h1 class="h3 mb-3">Eliminar cliente</h1>

<form class="card card-body bg-white" method="post" action="<?= $view->e($path) ?>/delete">
    <?= $view->csrfField() ?>
    <p class="alert alert-warning mb-3">
        ¿Eliminar a <strong><?= $view->e($customer->companyName->value()) ?></strong>
        (NIT <?= $view->e($customer->nit->formatted()) ?>)? Esta acción no se puede deshacer.
    </p>
    <div class="d-flex gap-2">
        <button class="btn btn-danger" type="submit">Sí, eliminar</button>
        <a class="btn btn-outline-secondary" href="<?= $view->e($path) ?>">No, volver</a>
    </div>
</form>
