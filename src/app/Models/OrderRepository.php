<?php
declare(strict_types=1);

namespace App\Models;

use Throwable;

final class OrderRepository extends Repository
{
    public const STATUSES = [
        'aberta'       => 'Aberta',
        'em_andamento' => 'Em andamento',
        'concluida'    => 'Concluída',
        'cancelada'    => 'Cancelada',
    ];

    protected function table(): string
    {
        return 'service_orders';
    }

    protected function fillable(): array
    {
        return ['client_id', 'user_id', 'status', 'subtotal', 'discount_percent', 'surcharge_percent', 'total', 'notes'];
    }

    public function listWithClients(string $status = ''): array
    {
        $sql = 'SELECT o.*, c.name AS client_name
                  FROM service_orders o
                  JOIN clients c ON c.id = o.client_id';
        $params = [];
        if ($status !== '' && isset(self::STATUSES[$status])) {
            $sql .= ' WHERE o.status = :status';
            $params['status'] = $status;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY o.created_at DESC');
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT o.*, c.name AS client_name, c.document AS client_document, c.cep AS client_cep,
                    c.address AS client_address, c.complement AS client_complement, u.name AS user_name
               FROM service_orders o
               JOIN clients c ON c.id = o.client_id
               JOIN users u ON u.id = o.user_id
              WHERE o.id = :id'
        );
        $stmt->execute(['id' => $id]);
        $order = $stmt->fetch();
        if (!$order) {
            return null;
        }
        $order['items'] = $this->items($id);
        return $order;
    }

    public function items(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT i.*, s.name AS service_name
               FROM service_order_items i
               JOIN services s ON s.id = i.service_id
              WHERE i.order_id = :id'
        );
        $stmt->execute(['id' => $orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Cria a OS e seus itens em uma única transação.
     * @param array $items lista de ['service_id', 'quantity', 'unit_price']
     */
    public function createWithItems(array $order, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->create($order);
            $this->insertItems($id, $items);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateWithItems(int $id, array $order, array $items): void
    {
        $this->db->beginTransaction();
        try {
            $this->update($id, $order);
            $this->db->prepare('DELETE FROM service_order_items WHERE order_id = :id')->execute(['id' => $id]);
            $this->insertItems($id, $items);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function insertItems(int $orderId, array $items): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO service_order_items (order_id, service_id, quantity, unit_price)
             VALUES (:order_id, :service_id, :quantity, :unit_price)'
        );
        foreach ($items as $item) {
            $stmt->execute([
                'order_id'   => $orderId,
                'service_id' => $item['service_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }
    }

    // ----------------------------------------------------------------
    // Métricas do dashboard (período inclusivo: $start 00:00 até $end 23:59)
    // ----------------------------------------------------------------

    public function metrics(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_orders,
                    COALESCE(SUM(CASE WHEN status <> 'cancelada' THEN total END), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN status = 'concluida' THEN total END), 0) AS revenue_done,
                    COUNT(DISTINCT client_id) AS unique_clients
               FROM service_orders
              WHERE created_at >= :start AND created_at < DATE_ADD(:end, INTERVAL 1 DAY)"
        );
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetch();
    }

    /** Clientes atendidos (distintos) e OS abertas por dia no período. */
    public function clientFlow(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT DATE(created_at) AS day,
                    COUNT(DISTINCT client_id) AS clients,
                    COUNT(*) AS orders
               FROM service_orders
              WHERE created_at >= :start AND created_at < DATE_ADD(:end, INTERVAL 1 DAY)
              GROUP BY DATE(created_at)
              ORDER BY day'
        );
        $stmt->execute(['start' => $start, 'end' => $end]);
        return array_column($stmt->fetchAll(), null, 'day');
    }

    public function statusBreakdown(string $start, string $end): array
    {
        $stmt = $this->db->prepare(
            'SELECT status, COUNT(*) AS qty
               FROM service_orders
              WHERE created_at >= :start AND created_at < DATE_ADD(:end, INTERVAL 1 DAY)
              GROUP BY status'
        );
        $stmt->execute(['start' => $start, 'end' => $end]);
        return array_column($stmt->fetchAll(), 'qty', 'status');
    }

    public function latest(int $limit = 5): array
    {
        $stmt = $this->db->prepare(
            'SELECT o.id, o.total, o.status, o.created_at, c.name AS client_name
               FROM service_orders o JOIN clients c ON c.id = o.client_id
              ORDER BY o.created_at DESC LIMIT :lim'
        );
        $stmt->bindValue('lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
