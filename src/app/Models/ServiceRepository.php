<?php
declare(strict_types=1);

namespace App\Models;

final class ServiceRepository extends Repository
{
    protected function table(): string
    {
        return 'services';
    }

    protected function fillable(): array
    {
        return ['name', 'description', 'price'];
    }

    public function search(string $term): array
    {
        $stmt = $this->db->prepare('SELECT * FROM services WHERE name LIKE :t ORDER BY name');
        $stmt->execute(['t' => "%{$term}%"]);
        return $stmt->fetchAll();
    }

    /** Retorna serviços indexados por id, para montar a OS. */
    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("SELECT * FROM services WHERE id IN ({$in})");
        $stmt->execute(array_values($ids));
        return array_column($stmt->fetchAll(), null, 'id');
    }

    public function isInUse(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM service_order_items WHERE service_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
