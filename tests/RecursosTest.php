<?php

declare(strict_types=1);

namespace Tests;

use App\Recursos;
use PHPUnit\Framework\TestCase;

final class RecursosTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('FEATURE_TESTE');
    }

    public function testRecursoDesligadoPorPadrao(): void
    {
        $this->assertFalse((new Recursos())->ativo('BUSCA'));
    }

    public function testRecursoLigadoExplicitamente(): void
    {
        $this->assertTrue((new Recursos(['BUSCA']))->ativo('busca'));
    }

    public function testLeVariavelDeAmbiente(): void
    {
        putenv('FEATURE_TESTE=true');
        $this->assertTrue(Recursos::doAmbiente()->ativo('TESTE'));

        putenv('FEATURE_TESTE=false');
        $this->assertFalse(Recursos::doAmbiente()->ativo('TESTE'));
    }
}
