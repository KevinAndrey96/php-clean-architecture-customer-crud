<?php
/**
 * @var CustomerCrud\Http\View                              $view
 * @var string                                              $title
 * @var string                                              $action
 * @var CustomerCrud\Application\Customer\CustomerInput     $input
 * @var array<string, string>                               $errors
 */

$fields = [
    ['nit', 'NIT', $input->nit, '860.007.386-1', 'Con o sin dígito de verificación; si lo incluye, se valida con el algoritmo de la DIAN.'],
    ['company_name', 'Razón social', $input->companyName, 'Andes Logística S.A.S.', null],
    ['address', 'Dirección', $input->address, 'Calle 26 # 68C-61', null],
    ['phone', 'Teléfono', $input->phone, '601 745 6321', 'Celular 3XX XXX XXXX o fijo 60X XXX XXXX.'],
    ['city', 'Ciudad', $input->city, 'Bogotá', null],
    ['credit_limit', 'Crédito aprobado (COP)', $input->creditLimit, '15.000.000', 'Pesos enteros; 0 si no tiene cupo.'],
];
?>
<h1 class="h3 mb-3"><?= $view->e($title) ?></h1>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger" role="alert">Revise los campos marcados.</div>
<?php endif ?>

<form class="card card-body bg-white" method="post" action="<?= $view->e($action) ?>" novalidate>
    <?= $view->csrfField() ?>
    <?php foreach ($fields as [$name, $label, $value, $placeholder, $hint]): ?>
        <?php $error = $errors[$name] ?? null ?>
        <div class="mb-3">
            <label class="form-label" for="<?= $view->e($name) ?>"><?= $view->e($label) ?></label>
            <input class="form-control<?= $error !== null ? ' is-invalid' : '' ?>" type="text"
                   id="<?= $view->e($name) ?>" name="<?= $view->e($name) ?>"
                   value="<?= $view->e($value) ?>" placeholder="<?= $view->e($placeholder) ?>" required>
            <?php if ($error !== null): ?>
                <div class="invalid-feedback"><?= $view->e($error) ?></div>
            <?php elseif ($hint !== null): ?>
                <div class="form-text"><?= $view->e($hint) ?></div>
            <?php endif ?>
        </div>
    <?php endforeach ?>
    <div class="d-flex gap-2">
        <button class="btn btn-success" type="submit">Guardar</button>
        <a class="btn btn-outline-secondary" href="/">Cancelar</a>
    </div>
</form>
