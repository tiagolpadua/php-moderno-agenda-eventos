<?php

declare(strict_types=1);

/*
 * Front controller: todas as requisições passam por aqui
 * (mesma ideia do public/index.php de um projeto Laravel).
 */

require dirname(__DIR__) . '/src/bootstrap.php';

// Servidor embutido do PHP (php -S): deixa servir arquivos estáticos existentes (css, imagens...).
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$pdo = App\Database::conectarPeloAmbiente();

// Facilita o uso local: cria o banco na primeira requisição.
// Em containers, desligamos isso (MIGRAR_AO_INICIAR=false) e rodamos bin/migrar.php uma única vez.
if (getenv('MIGRAR_AO_INICIAR') !== 'false') {
    App\Database::migrar($pdo);
}

$app = new App\Aplicacao($pdo);
$app->tratar(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
    $_POST,
    $_GET,
)->enviar();
