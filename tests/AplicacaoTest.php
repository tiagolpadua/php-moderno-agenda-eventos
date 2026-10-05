<?php

declare(strict_types=1);

namespace Tests;

use App\Aplicacao;
use App\Database;
use App\Recursos;
use PHPUnit\Framework\TestCase;

/**
 * Testa as rotas da aplicação usando um banco SQLite em memória.
 */
final class AplicacaoTest extends TestCase
{
    private Aplicacao $app;

    protected function setUp(): void
    {
        $this->app = $this->criarAplicacao(new Recursos());
    }

    private function criarAplicacao(Recursos $recursos): Aplicacao
    {
        $pdo = Database::conectar('sqlite::memory:');
        Database::migrar($pdo);

        return new Aplicacao($pdo, $recursos);
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

    public function testBuscaDesligadaIgnoraOTermoEEscondeOFormulario(): void
    {
        $resposta = $this->app->tratar('GET', '/', [], ['q' => 'redes']);

        $this->assertStringNotContainsString('role="search"', $resposta->corpo);
        $this->assertCount(3, json_decode($this->app->tratar('GET', '/api/eventos', [], ['q' => 'redes'])->corpo, true));
    }

    public function testBuscaLigadaFiltraPorTituloOuLocal(): void
    {
        $app = $this->criarAplicacao(new Recursos(['BUSCA']));

        $pagina = $app->tratar('GET', '/', [], ['q' => 'redes']);
        $this->assertStringContainsString('role="search"', $pagina->corpo);
        $this->assertStringContainsString('Ética médica e redes sociais', $pagina->corpo);
        $this->assertStringNotContainsString('Atualização em emergências clínicas', $pagina->corpo);

        $this->assertCount(1, json_decode($app->tratar('GET', '/api/eventos', [], ['q' => 'online'])->corpo, true));
    }
}
