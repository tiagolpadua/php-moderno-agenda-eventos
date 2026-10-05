<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Cria a conexão com o banco a partir de variáveis de ambiente.
 *
 *   DB_DSN       ex.: sqlite:var/data/agenda.sqlite  (padrão)
 *                     mysql:host=db;port=3306;dbname=agenda;charset=utf8mb4
 *   DB_USER      (não usado no SQLite)
 *   DB_PASSWORD  (não usado no SQLite)
 */
final class Database
{
    public static function conectarPeloAmbiente(): PDO
    {
        $dsn = getenv('DB_DSN');
        if ($dsn === false || $dsn === '') {
            $pasta = dirname(__DIR__) . '/var/data';
            if (!is_dir($pasta)) {
                mkdir($pasta, 0775, true);
            }
            $dsn = "sqlite:{$pasta}/agenda.sqlite";
        }

        return self::conectar($dsn, getenv('DB_USER') ?: null, getenv('DB_PASSWORD') ?: null);
    }

    public static function conectar(string $dsn, ?string $usuario = null, ?string $senha = null): PDO
    {
        return new PDO($dsn, $usuario, $senha, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    /**
     * Cria a tabela (se não existir) e insere alguns eventos de exemplo
     * quando a tabela estiver vazia. Funciona em SQLite, MySQL e PostgreSQL.
     */
    public static function migrar(PDO $pdo): void
    {
        $id = match ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            'mysql' => 'INT AUTO_INCREMENT PRIMARY KEY',
            'pgsql' => 'SERIAL PRIMARY KEY',
            default => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        };

        $pdo->exec(<<<SQL
            CREATE TABLE IF NOT EXISTS eventos (
                id {$id},
                titulo VARCHAR(120) NOT NULL,
                data DATE NOT NULL,
                local VARCHAR(120) NOT NULL
            )
            SQL);

        if ((int) $pdo->query('SELECT COUNT(*) FROM eventos')->fetchColumn() === 0) {
            $repositorio = new EventoRepository($pdo);
            $repositorio->criar(new Evento(null, 'Boas práticas em prontuário eletrônico', '2026-11-10', 'Auditório principal'));
            $repositorio->criar(new Evento(null, 'Ética médica e redes sociais', '2026-11-24', 'Online'));
            $repositorio->criar(new Evento(null, 'Atualização em emergências clínicas', '2026-12-08', 'Sala 3'));
        }
    }
}
