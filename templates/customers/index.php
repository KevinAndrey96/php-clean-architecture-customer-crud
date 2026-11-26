<?php
/**
 * @var CustomerCrud\Http\View                      $view
 * @var list<CustomerCrud\Domain\Customer\Customer> $customers
 * @var string                                      $term
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Clientes</h1>
    <form class="d-flex gap-2" method="get" action="/" role="search">
        <input class="form-control" type="search" name="q" value="<?= $view->e($term) ?>"
               placeholder="NIT, razón social o ciudad" aria-label="Buscar clientes" maxlength="80">
        <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    </form>
</div>

<?php if ($customers === []): ?>
    <div class="alert alert-info">
        <?= $term === '' ? 'Aún no hay clientes registrados.' : 'Ningún cliente coincide con "'.$view->e($term).'".' ?>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle bg-white">
            <thead class="table-dark">
            <tr>
                <th scope="col">NIT</th>
                <th scope="col">Razón social</th>
                <th scope="col">Ciudad</th>
                <th scope="col">Teléfono</th>
                <th scope="col" class="text-end">Crédito aprobado</th>
                <th scope="col" class="text-end">Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $customer): ?>
                <?php $path = '/customers/'.$customer->nit->body() ?>
                <tr>
                    <td class="text-nowrap"><?= $view->e($customer->nit->formatted()) ?></td>
                    <td><?= $view->e($customer->companyName->value()) ?></td>
                    <td><?= $view->e($customer->city->value()) ?></td>
                    <td class="text-nowrap"><?= $view->e($customer->phone->formatted()) ?></td>
                    <td class="text-end text-nowrap"><?= $view->e($customer->creditLimit->formatted()) ?></td>
                    <td class="text-end text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="<?= $view->e($path) ?>">Ver</a>
                        <a class="btn btn-sm btn-outline-success" href="<?= $view->e($path) ?>/edit">Editar</a>
                        <a class="btn btn-sm btn-outline-danger" href="<?= $view->e($path) ?>/delete">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach ?>
            </tbody>
        </table>
    </div>
    <p class="text-muted small"><?= count($customers) ?> cliente(s)</p>
<?php endif ?>
