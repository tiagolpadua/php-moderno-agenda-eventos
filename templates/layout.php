<?php

use function App\e;

/** @var string $titulo */
/** @var string $conteudo */
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
<header>
    <h1><?= e($titulo) ?></h1>
    <p class="subtitulo">Mentoria CREMESP · PHP Moderno · Turma 2026</p>
</header>
<main>
    <?= $conteudo ?>
</main>
<footer>
    <small>Projeto-base da trilha DevOps · <a href="/api/eventos">API</a> · <a href="/health">health</a></small>
</footer>
</body>
</html>
