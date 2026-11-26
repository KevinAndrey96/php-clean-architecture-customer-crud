<?php
/**
 * @var CustomerCrud\Http\View $view
 * @var int                    $status
 * @var string                 $message
 */
?>
<div class="card card-body bg-white text-center">
    <h1 class="display-6"><?= $view->e($status) ?></h1>
    <p class="lead"><?= $view->e($message) ?></p>
    <p class="mb-0"><a class="btn btn-outline-secondary" href="/">Volver al listado</a></p>
</div>
