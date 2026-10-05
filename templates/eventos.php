<?php

use function App\e;

/** @var list<App\Evento> $eventos */
/** @var array<string, string> $erros */
/** @var array<string, mixed> $antigos */
/** @var bool $sucesso */
/** @var bool $buscaAtiva */
/** @var string|null $termo */
?>
<?php if ($sucesso): ?>
    <p class="alerta sucesso" role="status">Evento cadastrado com sucesso!</p>
<?php endif; ?>

<section>
    <h2>Próximos eventos</h2>
    <?php if ($buscaAtiva): ?>
        <form method="get" action="/" role="search" class="busca">
            <label for="q">Buscar por título ou local</label>
            <input id="q" name="q" type="search" value="<?= e($termo ?? '') ?>">
            <button type="submit">Buscar</button>
        </form>
    <?php endif; ?>
    <?php if ($eventos === []): ?>
        <p><?= $termo !== null ? 'Nenhum evento encontrado para "' . e($termo) . '".' : 'Nenhum evento cadastrado.' ?></p>
    <?php else: ?>
        <table>
            <thead>
            <tr><th>Data</th><th>Evento</th><th>Local</th></tr>
            </thead>
            <tbody>
            <?php foreach ($eventos as $evento): ?>
                <tr>
                    <td><?= e(date('d/m/Y', strtotime($evento->data))) ?></td>
                    <td><?= e($evento->titulo) ?></td>
                    <td><?= e($evento->local) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section>
    <h2>Cadastrar evento</h2>
    <form method="post" action="/eventos" novalidate>
        <?php foreach (['titulo' => 'Título', 'data' => 'Data', 'local' => 'Local'] as $campo => $rotulo): ?>
            <div class="campo">
                <label for="<?= $campo ?>"><?= $rotulo ?></label>
                <input id="<?= $campo ?>" name="<?= $campo ?>"
                       type="<?= $campo === 'data' ? 'date' : 'text' ?>"
                       value="<?= e($antigos[$campo] ?? '') ?>"
                       <?= isset($erros[$campo]) ? 'aria-invalid="true" aria-describedby="erro-' . $campo . '"' : '' ?>>
                <?php if (isset($erros[$campo])): ?>
                    <span class="erro" id="erro-<?= $campo ?>"><?= e($erros[$campo]) ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit">Cadastrar</button>
    </form>
</section>
