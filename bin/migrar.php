<?php

declare(strict_types=1);

/*
 * Cria as tabelas e os dados de exemplo.
 * Uso: php bin/migrar.php   (equivalente ao "php artisan migrate" do Laravel)
 */

require dirname(__DIR__) . '/src/bootstrap.php';

App\Database::migrar(App\Database::conectarPeloAmbiente());

echo "Banco de dados pronto.\n";
