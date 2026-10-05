<?php

declare(strict_types=1);

/*
 * Front controller: todas as requisições passam por aqui
 * (mesma ideia do public/index.php de um projeto Laravel).
 */

$raiz = dirname(__DIR__);

if (is_file("{$raiz}/vendor/autoload.php")) {
    require "{$raiz}/vendor/autoload.php";
} else {
    // Permite rodar sem `composer install` (ex.: só para ver a aplicação funcionando).
    spl_autoload_register(static function (string $classe) use ($raiz): void {
        if (str_starts_with($classe, 'App\\')) {
            $arquivo = "{$raiz}/src/" . str_replace('\\', '/', substr($classe, 4)) . '.php';
            if (is_file($arquivo)) {
                require $arquivo;
            }
        }
    });
}

// Servidor embutido do PHP (php -S): deixa servir arquivos estáticos existentes (css, imagens...).
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$pdo = App\Database::conectarPeloAmbiente();
App\Database::migrar($pdo);

$app = new App\Aplicacao($pdo);
$app->tratar(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
    $_POST,
    $_GET,
)->enviar();
