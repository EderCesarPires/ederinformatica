<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderRepository;
use App\Models\TaskRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = $this->requireAuth();

        // Período padrão: mês atual
        [$start, $end] = $this->resolvePeriod($_GET['start'] ?? '', $_GET['end'] ?? '');

        $orders = new OrderRepository();
        $flow = $orders->clientFlow($start, $end);

        // Preenche todos os dias do período (dias sem OS aparecem com zero)
        $labels = $clients = $ordersPerDay = [];
        $period = new DatePeriod(
            new DateTimeImmutable($start),
            new DateInterval('P1D'),
            (new DateTimeImmutable($end))->modify('+1 day')
        );
        foreach ($period as $day) {
            $key = $day->format('Y-m-d');
            $labels[] = $day->format('d/m');
            $clients[] = (int) ($flow[$key]['clients'] ?? 0);
            $ordersPerDay[] = (int) ($flow[$key]['orders'] ?? 0);
        }

        $this->view('dashboard/index', [
            'title'     => 'Dashboard',
            'start'     => $start,
            'end'       => $end,
            'metrics'   => $orders->metrics($start, $end),
            'statuses'  => $orders->statusBreakdown($start, $end),
            'latest'    => $orders->latest(),
            'tasks'     => (new TaskRepository())->forUser($user['id']),
            'chart'     => ['labels' => $labels, 'clients' => $clients, 'orders' => $ordersPerDay],
        ]);
    }

    /** Valida as datas do filtro; se inválidas, usa o mês atual. Limita a 366 dias. */
    private function resolvePeriod(string $start, string $end): array
    {
        $s = DateTimeImmutable::createFromFormat('!Y-m-d', $start);
        $e = DateTimeImmutable::createFromFormat('!Y-m-d', $end);

        if (!$s || !$e) {
            $s = new DateTimeImmutable('first day of this month');
            $e = new DateTimeImmutable('last day of this month');
        }
        if ($s > $e) {
            [$s, $e] = [$e, $s];
        }
        if ($s->diff($e)->days > 366) {
            $s = $e->modify('-366 days');
        }
        return [$s->format('Y-m-d'), $e->format('Y-m-d')];
    }
}
