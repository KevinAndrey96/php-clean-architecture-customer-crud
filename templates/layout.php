<?php
/**
 * @var CustomerCrud\Http\View $view
 * @var string                 $title
 * @var string                 $content
 * @var string|null            $flash
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $view->e($title) ?> · Registro de clientes</title>
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/">Registro de clientes</a>
        <a class="btn btn-outline-light btn-sm" href="/customers/new">Nuevo cliente</a>
    </div>
</nav>
<main class="container">
    <?php if ($flash !== null): ?>
        <div class="alert alert-success" role="status"><?= $view->e($flash) ?></div>
    <?php endif ?>
    <?= $content ?>
</main>
</body>
</html>
