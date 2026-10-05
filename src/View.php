<?php

declare(strict_types=1);

namespace App;

/**
 * Renderiza templates PHP da pasta templates/ dentro do layout.
 */
final class View
{
    /**
     * @param array<string, mixed> $dados variáveis disponíveis no template
     */
    public static function renderizar(string $template, array $dados = []): string
    {
        $conteudo = self::incluir($template, $dados);

        return self::incluir('layout', ['conteudo' => $conteudo, 'titulo' => Aplicacao::NOME]);
    }

    /**
     * @param array<string, mixed> $dados
     */
    private static function incluir(string $template, array $dados): string
    {
        extract($dados);
        ob_start();
        require dirname(__DIR__) . "/templates/{$template}.php";

        return (string) ob_get_clean();
    }
}

/**
 * Escapa texto para exibição segura em HTML.
 */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
