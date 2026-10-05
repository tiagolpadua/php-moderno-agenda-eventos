<?php

declare(strict_types=1);

namespace Tests;

use App\Evento;
use PHPUnit\Framework\TestCase;

final class EventoTest extends TestCase
{
    public function testDadosValidosNaoGeramErros(): void
    {
        $erros = Evento::validar(['titulo' => 'Palestra', 'data' => '2026-11-10', 'local' => 'Online']);

        $this->assertSame([], $erros);
    }

    public function testCamposObrigatorios(): void
    {
        $erros = Evento::validar([]);

        $this->assertArrayHasKey('titulo', $erros);
        $this->assertArrayHasKey('data', $erros);
        $this->assertArrayHasKey('local', $erros);
    }

    public function testDataInvalidaEhRejeitada(): void
    {
        $erros = Evento::validar(['titulo' => 'Palestra', 'data' => '2026-02-30', 'local' => 'Online']);

        $this->assertArrayHasKey('data', $erros);
    }

    public function testTituloMuitoLongoEhRejeitado(): void
    {
        $erros = Evento::validar(['titulo' => str_repeat('a', 121), 'data' => '2026-11-10', 'local' => 'Online']);

        $this->assertArrayHasKey('titulo', $erros);
    }
}
