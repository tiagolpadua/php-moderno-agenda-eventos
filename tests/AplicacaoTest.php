<?php

declare(strict_types=1);

namespace Tests;

use App\Aplicacao;
use App\Database;
use PHPUnit\Framework\TestCase;

/**
 * Testa as rotas da aplicação usando um banco SQLite em memória.
 */
final class AplicacaoTest extends TestCase
{
    private Aplicacao $app;

    protected function setUp(): void
    {
        $pdo = Database::conectar('sqlite::memory:');
        Database::migrar($pdo);
        $this->app = new Aplicacao($pdo);
    }

    public function testPaginaInicialListaEventos(): void
    {
        $resposta = $this->app->tratar('GET', '/');

        $this->assertSame(200, $resposta->status);
        $this->assertStringContainsString('Próximos eventos', $resposta->corpo);
        $this->assertStringContainsString('Ética médica e redes sociais', $resposta->corpo);
    }

    public function testApiRetornaEventosEmJson(): void
    {
        $resposta = $this->app->tratar('GET', '/api/eventos');
        $eventos = json_decode($resposta->corpo, true);

        $this->assertSame(200, $resposta->status);
        $this->assertStringStartsWith('application/json', $resposta->cabecalhos['Content-Type']);
        $this->assertCount(3, $eventos);
    }

    public function testCadastroValidoRedirecionaEPersiste(): void
    {
        $resposta = $this->app->tratar('POST', '/eventos', [
            'titulo' => 'Novo evento',
            'data' => '2026-12-15',
            'local' => 'Auditório',
        ]);

        $this->assertSame(303, $resposta->status);
        $this->assertSame('/?cadastrado=1', $resposta->cabecalhos['Location']);
        $this->assertCount(4, json_decode($this->app->tratar('GET', '/api/eventos')->corpo, true));
    }

    public function testCadastroInvalidoRetorna422(): void
    {
        $resposta = $this->app->tratar('POST', '/eventos', ['titulo' => '']);

        $this->assertSame(422, $resposta->status);
        $this->assertStringContainsString('Informe o título do evento.', $resposta->corpo);
    }

    public function testEventoInexistenteRetorna404(): void
    {
        $this->assertSame(404, $this->app->tratar('GET', '/api/eventos/999')->status);
    }

    public function testHealthIndicaQueEstaSaudavel(): void
    {
        $resposta = $this->app->tratar('GET', '/health');

        $this->assertSame(200, $resposta->status);
        $this->assertSame('ok', json_decode($resposta->corpo, true)['status']);
        $this->assertArrayHasKey('X-App-Instance', $resposta->cabecalhos);
    }
}
