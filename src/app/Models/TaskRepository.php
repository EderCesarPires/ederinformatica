<?php
declare(strict_types=1);

namespace App\Models;

final class TaskRepository extends Repository
{
    protected function table(): string
    {
        return 'tasks';
    }

    protected function fillable(): array
    {
        return ['user_id', 'title', 'done'];
    }

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE user_id = :uid ORDER BY done ASC, created_at DESC');
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }

    /** Busca a tarefa garantindo que pertence ao usuário. */
    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM tasks WHERE id = :id AND user_id = :uid');
        $stmt->execute(['id' => $id, 'uid' => $userId]);
        return $stmt->fetch() ?: null;
    }
}
