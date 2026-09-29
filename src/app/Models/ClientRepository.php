<?php
declare(strict_types=1);

namespace App\Models;

final class ClientRepository extends Repository
{
    protected function table(): string
    {
        return 'clients';
    }

    protected function fillable(): array
    {
        return ['name', 'document', 'cep', 'address', 'complement'];
    }

    public function search(string $term): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM clients WHERE name LIKE :t OR document LIKE :d ORDER BY name'
        );
        $stmt->execute(['t' => "%{$term}%", 'd' => '%' . preg_replace('/\D/', '', $term) . '%']);
        return $stmt->fetchAll();
    }

    public function documentExists(string $document, ?int $ignoreId = null): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM clients WHERE document = :doc AND id <> :id');
        $stmt->execute(['doc' => $document, 'id' => $ignoreId ?? 0]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function hasOrders(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM service_orders WHERE client_id = :id');
        $stmt->execute(['id' => $id]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
