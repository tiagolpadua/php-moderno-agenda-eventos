<?php

declare(strict_types=1);

/*
 * Carrega as classes da aplicação. Usa o autoload do Composer quando existir
 * e, sem ele, um carregador simples (permite rodar sem "composer install").
 */

$raiz = dirname(__DIR__);

if (is_file("{$raiz}/vendor/autoload.php")) {
    require_once "{$raiz}/vendor/autoload.php";

    return;
}

spl_autoload_register(static function (string $classe) use ($raiz): void {
    if (str_starts_with($classe, 'App\\')) {
        $arquivo = "{$raiz}/src/" . str_replace('\\', '/', substr($classe, 4)) . '.php';
        if (is_file($arquivo)) {
            require $arquivo;
        }
    }
});
