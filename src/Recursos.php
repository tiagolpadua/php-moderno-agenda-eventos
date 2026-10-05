<?php

declare(strict_types=1);

namespace App;

/**
 * Feature toggles (feature flags) da aplicação.
 *
 * Um recurso fica ligado quando existe a variável de ambiente FEATURE_<NOME>
 * com valor "true", "1", "on" ou "yes". Exemplo: FEATURE_BUSCA=true
 *
 * Toggles deste tipo ("release toggles") permitem integrar na main um código
 * ainda não liberado para os usuários, e ligá-lo depois sem novo deploy de código.
 */
final readonly class Recursos
{
    /**
     * @param list<string> $ativos nomes dos recursos ligados (ex.: ['BUSCA'])
     */
    public function __construct(private array $ativos = [])
    {
    }

    public static function doAmbiente(): self
    {
        $ativos = [];
        foreach (getenv() as $variavel => $valor) {
            if (str_starts_with($variavel, 'FEATURE_')
                && in_array(strtolower(trim($valor)), ['true', '1', 'on', 'yes'], true)) {
                $ativos[] = substr($variavel, strlen('FEATURE_'));
            }
        }

        return new self($ativos);
    }

    public function ativo(string $nome): bool
    {
        return in_array(strtoupper($nome), $this->ativos, true);
    }
}
