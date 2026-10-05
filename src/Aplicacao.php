<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Recebe a requisição (método + caminho) e devolve uma Resposta.
 *
 * Rotas:
 *   GET  /                 lista de eventos + formulário (HTML)
 *   POST /eventos          cadastra um evento
 *   GET  /api/eventos      lista de eventos (JSON)
 *   GET  /api/eventos/{id} um evento (JSON)
 *   GET  /health           verificação de saúde (JSON)
 */
final class Aplicacao
{
    public const string NOME = 'Agenda de Eventos';

    private readonly EventoRepository $eventos;

    public function __construct(private readonly PDO $pdo)
    {
        $this->eventos = new EventoRepository($pdo);
    }

    /**
     * @param array<string, mixed> $post  dados do formulário ($_POST)
     * @param array<string, mixed> $query parâmetros da URL ($_GET)
     */
    public function tratar(string $metodo, string $caminho, array $post = [], array $query = []): Resposta
    {
        $caminho = '/' . trim($caminho, '/');

        $resposta = match (true) {
            $metodo === 'GET' && $caminho === '/' => $this->paginaInicial($query),
            $metodo === 'POST' && $caminho === '/eventos' => $this->cadastrar($post),
            $metodo === 'GET' && $caminho === '/api/eventos' => $this->apiListar(),
            $metodo === 'GET' && preg_match('#^/api/eventos/(\d+)$#', $caminho, $m) === 1 => $this->apiBuscar((int) $m[1]),
            $metodo === 'GET' && $caminho === '/health' => $this->health(),
            default => Resposta::json(['erro' => 'Rota não encontrada'], 404),
        };

        // Identifica qual instância respondeu (útil quando houver várias réplicas atrás de um balanceador).
        $resposta->cabecalhos['X-App-Instance'] = (string) gethostname();

        return $resposta;
    }

    /**
     * @param array<string, mixed> $query
     */
    private function paginaInicial(array $query, array $erros = [], array $antigos = [], int $status = 200): Resposta
    {
        return Resposta::html(View::renderizar('eventos', [
            'eventos' => $this->eventos->listar(),
            'erros' => $erros,
            'antigos' => $antigos,
            'sucesso' => isset($query['cadastrado']),
        ]), $status);
    }

    /**
     * @param array<string, mixed> $post
     */
    private function cadastrar(array $post): Resposta
    {
        $erros = Evento::validar($post);
        if ($erros !== []) {
            return $this->paginaInicial([], $erros, $post, 422);
        }

        $this->eventos->criar(Evento::deFormulario($post));

        return Resposta::redirecionar('/?cadastrado=1');
    }

    private function apiListar(): Resposta
    {
        return Resposta::json(array_map(fn (Evento $e) => $e->paraArray(), $this->eventos->listar()));
    }

    private function apiBuscar(int $id): Resposta
    {
        $evento = $this->eventos->buscar($id);

        return $evento === null
            ? Resposta::json(['erro' => 'Evento não encontrado'], 404)
            : Resposta::json($evento->paraArray());
    }

    private function health(): Resposta
    {
        try {
            $this->pdo->query('SELECT 1');
            $banco = 'ok';
        } catch (\Throwable) {
            $banco = 'erro';
        }

        return Resposta::json([
            'status' => $banco === 'ok' ? 'ok' : 'degradado',
            'banco' => $banco,
            'instancia' => gethostname(),
            'php' => PHP_VERSION,
        ], $banco === 'ok' ? 200 : 503);
    }
}
