<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\ClientRepository;
use App\Models\OrderRepository;
use App\Models\ServiceRepository;
use App\Pricing\AdjustmentFactory;
use App\Pricing\PriceCalculator;

final class OrderController extends Controller
{
    private OrderRepository $orders;
    private array $user;

    public function __construct()
    {
        $this->user = $this->requireAuth();
        $this->orders = new OrderRepository();
    }

    public function index(): void
    {
        $status = $_GET['status'] ?? '';
        $this->view('orders/index', [
            'title'    => 'Ordens de Serviço',
            'status'   => $status,
            'statuses' => OrderRepository::STATUSES,
            'orders'   => $this->orders->listWithClients($status),
        ]);
    }

    public function show(int $id): void
    {
        $order = $this->orders->findWithDetails($id) ?? $this->notFound();
        $this->view('orders/show', [
            'title'    => 'OS #' . $id,
            'order'    => $order,
            'statuses' => OrderRepository::STATUSES,
        ]);
    }

    public function create(): void
    {
        $this->form('Nova Ordem de Serviço', null);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        [$order, $items] = $this->validated('/orders/create');
        $id = $this->orders->createWithItems($order + ['user_id' => $this->user['id']], $items);
        Session::flash('success', "OS #{$id} criada com sucesso.");
        $this->redirect("/orders/{$id}");
    }

    public function edit(int $id): void
    {
        $order = $this->orders->findWithDetails($id) ?? $this->notFound();
        $this->form("Editar OS #{$id}", $order);
    }

    public function update(int $id): void
    {
        $this->verifyCsrf();
        $this->orders->find($id) ?? $this->notFound();
        [$order, $items] = $this->validated("/orders/{$id}/edit");
        $this->orders->updateWithItems($id, $order, $items);
        Session::flash('success', "OS #{$id} atualizada com sucesso.");
        $this->redirect("/orders/{$id}");
    }

    public function destroy(int $id): void
    {
        $this->verifyCsrf();
        $this->orders->find($id) ?? $this->notFound();
        $this->orders->delete($id);
        Session::flash('success', "OS #{$id} excluída.");
        $this->redirect('/orders');
    }

    private function form(string $title, ?array $order): void
    {
        $this->view('orders/form', [
            'title'    => $title,
            'order'    => $order,
            'clients'  => (new ClientRepository())->all('name ASC'),
            'services' => (new ServiceRepository())->all('name ASC'),
            'statuses' => OrderRepository::STATUSES,
        ]);
    }

    /**
     * Valida o formulário e calcula os valores usando Strategy + Factory.
     * @return array{0: array, 1: array} [dados da OS, itens]
     */
    private function validated(string $backTo): array
    {
        $errors = [];

        $clientId = (int) ($_POST['client_id'] ?? 0);
        if (!(new ClientRepository())->find($clientId)) {
            $errors[] = 'Selecione um cliente válido.';
        }

        $status = $_POST['status'] ?? 'aberta';
        if (!isset(OrderRepository::STATUSES[$status])) {
            $errors[] = 'Status inválido.';
        }

        $discount = (float) str_replace(',', '.', $this->input('discount_percent', '0'));
        $surcharge = (float) str_replace(',', '.', $this->input('surcharge_percent', '0'));
        if ($discount < 0 || $discount > 100) {
            $errors[] = 'O desconto deve estar entre 0% e 100%.';
        }
        if ($surcharge < 0 || $surcharge > 100) {
            $errors[] = 'O acréscimo deve estar entre 0% e 100%.';
        }

        // Itens: service_ids[] e quantities[] vêm em paralelo
        $serviceIds = array_map('intval', (array) ($_POST['service_ids'] ?? []));
        $quantities = array_map('intval', (array) ($_POST['quantities'] ?? []));
        $catalog = (new ServiceRepository())->findMany(array_unique(array_filter($serviceIds)));

        $items = [];
        foreach ($serviceIds as $i => $serviceId) {
            if (!isset($catalog[$serviceId])) {
                continue;
            }
            $qty = max(1, min(999, $quantities[$i] ?? 1));
            // Agrupa serviços repetidos somando a quantidade
            if (isset($items[$serviceId])) {
                $items[$serviceId]['quantity'] += $qty;
            } else {
                $items[$serviceId] = [
                    'service_id' => $serviceId,
                    'quantity'   => $qty,
                    'unit_price' => (float) $catalog[$serviceId]['price'],
                ];
            }
        }
        if ($items === []) {
            $errors[] = 'Adicione pelo menos um serviço à OS.';
        }

        if ($errors) {
            $this->backWithErrors($backTo, $errors);
        }

        $calculator = new PriceCalculator();
        foreach (AdjustmentFactory::fromPercentages($discount, $surcharge) as $adjustment) {
            $calculator->addAdjustment($adjustment);
        }

        $items = array_values($items);
        $order = [
            'client_id'         => $clientId,
            'status'            => $status,
            'subtotal'          => $calculator->subtotal($items),
            'discount_percent'  => $discount,
            'surcharge_percent' => $surcharge,
            'total'             => $calculator->total($items),
            'notes'             => $this->input('notes') ?: null,
        ];

        return [$order, $items];
    }
}
