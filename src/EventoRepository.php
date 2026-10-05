<?php

declare(strict_types=1);

namespace App;

use PDO;

/**
 * Acesso aos eventos no banco de dados.
 */
final class EventoRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param string|null $termo filtra por título ou local (opcional)
     * @return list<Evento> eventos ordenados por data
     */
    public function listar(?string $termo = null): array
    {
        if ($termo === null) {
            $linhas = $this->pdo->query('SELECT id, titulo, data, local FROM eventos ORDER BY data, id')->fetchAll();
        } else {
            $stmt = $this->pdo->prepare(
                'SELECT id, titulo, data, local FROM eventos WHERE titulo LIKE :termo OR local LIKE :termo2 ORDER BY data, id'
            );
            $stmt->execute(['termo' => "%{$termo}%", 'termo2' => "%{$termo}%"]);
            $linhas = $stmt->fetchAll();
        }

        return array_map(self::hidratar(...), $linhas);
    }

    public function buscar(int $id): ?Evento
    {
        $stmt = $this->pdo->prepare('SELECT id, titulo, data, local FROM eventos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $linha = $stmt->fetch();

        return $linha === false ? null : self::hidratar($linha);
    }

    public function criar(Evento $evento): Evento
    {
        $stmt = $this->pdo->prepare('INSERT INTO eventos (titulo, data, local) VALUES (:titulo, :data, :local)');
        $stmt->execute([
            'titulo' => $evento->titulo,
            'data' => $evento->data,
            'local' => $evento->local,
        ]);

        return new Evento((int) $this->pdo->lastInsertId(), $evento->titulo, $evento->data, $evento->local);
    }

    /**
     * @param array<string, mixed> $linha
     */
    private static function hidratar(array $linha): Evento
    {
        return new Evento((int) $linha['id'], (string) $linha['titulo'], (string) $linha['data'], (string) $linha['local']);
    }
}
