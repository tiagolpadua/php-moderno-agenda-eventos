<?php

declare(strict_types=1);

namespace App;

/**
 * Um evento da agenda (curso, palestra, congresso...).
 */
final readonly class Evento
{
    public function __construct(
        public ?int $id,
        public string $titulo,
        public string $data,
        public string $local,
    ) {
    }

    /**
     * Valida os dados vindos de um formulário.
     *
     * @param array<string, mixed> $dados
     * @return array<string, string> erros indexados pelo nome do campo (vazio = válido)
     */
    public static function validar(array $dados): array
    {
        $erros = [];

        $titulo = trim((string) ($dados['titulo'] ?? ''));
        if ($titulo === '') {
            $erros['titulo'] = 'Informe o título do evento.';
        } elseif (mb_strlen($titulo) > 120) {
            $erros['titulo'] = 'O título deve ter no máximo 120 caracteres.';
        }

        $data = (string) ($dados['data'] ?? '');
        $convertida = \DateTimeImmutable::createFromFormat('!Y-m-d', $data);
        if ($convertida === false || $convertida->format('Y-m-d') !== $data) {
            $erros['data'] = 'Informe uma data válida (AAAA-MM-DD).';
        }

        if (trim((string) ($dados['local'] ?? '')) === '') {
            $erros['local'] = 'Informe o local do evento.';
        }

        return $erros;
    }

    /**
     * @param array<string, mixed> $dados dados já validados
     */
    public static function deFormulario(array $dados): self
    {
        return new self(
            id: null,
            titulo: trim((string) $dados['titulo']),
            data: (string) $dados['data'],
            local: trim((string) $dados['local']),
        );
    }

    /**
     * @return array{id: ?int, titulo: string, data: string, local: string}
     */
    public function paraArray(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'data' => $this->data,
            'local' => $this->local,
        ];
    }
}
