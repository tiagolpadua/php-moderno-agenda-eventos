<?php

declare(strict_types=1);

namespace App;

/**
 * Resposta HTTP simples (status, cabeçalhos e corpo).
 */
final class Resposta
{
    /**
     * @param array<string, string> $cabecalhos
     */
    public function __construct(
        public readonly int $status,
        public readonly string $corpo,
        public array $cabecalhos = [],
    ) {
    }

    public static function html(string $corpo, int $status = 200): self
    {
        return new self($status, $corpo, ['Content-Type' => 'text/html; charset=utf-8']);
    }

    /**
     * @param array<mixed> $dados
     */
    public static function json(array $dados, int $status = 200): self
    {
        $corpo = json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return new self($status, $corpo, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    public static function redirecionar(string $destino): self
    {
        return new self(303, '', ['Location' => $destino]);
    }

    public function enviar(): void
    {
        http_response_code($this->status);
        foreach ($this->cabecalhos as $nome => $valor) {
            header("{$nome}: {$valor}");
        }
        echo $this->corpo;
    }
}
