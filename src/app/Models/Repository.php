<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * PADRÃO DE PROJETO: Repository
 *
 * Isola todo o acesso a dados (SQL) da camada de controle. Os controllers
 * nunca escrevem SQL: pedem dados aos repositórios. A classe base fornece
 * as operações genéricas de CRUD; cada repositório concreto informa sua
 * tabela e as colunas que aceita gravar.
 */
abstract class Repository
{
    protected PDO $db;

    /** Nome da tabela no banco. */
    abstract protected function table(): string;

    /** Colunas que podem ser gravadas via create/update (whitelist). */
    abstract protected function fillable(): array;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function all(string $orderBy = 'id DESC'): array
    {
        return $this->db->query("SELECT * FROM {$this->table()} ORDER BY {$orderBy}")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table()} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $data = $this->onlyFillable($data);
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn ($c) => ":{$c}", array_keys($data)));

        $stmt = $this->db->prepare("INSERT INTO {$this->table()} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $data = $this->onlyFillable($data);
        $set = implode(', ', array_map(fn ($c) => "{$c} = :{$c}", array_keys($data)));

        $stmt = $this->db->prepare("UPDATE {$this->table()} SET {$set} WHERE id = :id");
        return $stmt->execute($data + ['id' => $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table()} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    private function onlyFillable(array $data): array
    {
        return array_intersect_key($data, array_flip($this->fillable()));
    }
}
