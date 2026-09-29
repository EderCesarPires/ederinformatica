<?php
$statusLabels = App\Models\OrderRepository::STATUSES;
$pending = array_filter($tasks, fn ($t) => !$t['done']);
?>
<form method="get" action="/dashboard" class="card period-filter">
    <div class="field inline">
        <label for="start"><i class="bi bi-calendar3"></i> De</label>
        <input type="date" id="start" name="start" value="<?= e($start) ?>">
    </div>
    <div class="field inline">
        <label for="end">Até</label>
        <input type="date" id="end" name="end" value="<?= e($end) ?>">
    </div>
    <button class="btn btn-primary"><i class="bi bi-funnel"></i> Filtrar</button>
    <a href="/dashboard" class="btn btn-ghost">Mês atual</a>
    <span class="period-label">
        Período: <strong><?= date('d/m/Y', strtotime($start)) ?></strong> a <strong><?= date('d/m/Y', strtotime($end)) ?></strong>
    </span>
</form>

<section class="stats">
    <div class="stat">
        <span class="stat-icon"><i class="bi bi-clipboard-check"></i></span>
        <div>
            <small>Ordens de serviço</small>
            <strong><?= (int) $metrics['total_orders'] ?></strong>
        </div>
    </div>
    <div class="stat">
        <span class="stat-icon"><i class="bi bi-cash-coin"></i></span>
        <div>
            <small>Valor ganho</small>
            <strong><?= money($metrics['revenue']) ?></strong>
            <em>exclui canceladas</em>
        </div>
    </div>
    <div class="stat">
        <span class="stat-icon"><i class="bi bi-check2-all"></i></span>
        <div>
            <small>Recebido (concluídas)</small>
            <strong><?= money($metrics['revenue_done']) ?></strong>
        </div>
    </div>
    <div class="stat">
        <span class="stat-icon"><i class="bi bi-people"></i></span>
        <div>
            <small>Clientes atendidos</small>
            <strong><?= (int) $metrics['unique_clients'] ?></strong>
        </div>
    </div>
</section>

<section class="grid-dashboard">
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-graph-up"></i> Fluxo de clientes</h3>
            <span class="muted">clientes atendidos e OS abertas por dia</span>
        </div>
        <div class="chart-box"><canvas id="flowChart"></canvas></div>
        <div class="status-pills">
            <?php foreach ($statusLabels as $key => $label): ?>
                <span class="badge badge-<?= e($key) ?>"><?= e($label) ?>: <?= (int) ($statuses[$key] ?? 0) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card" id="tarefas">
        <div class="card-header">
            <h3><i class="bi bi-list-check"></i> Minhas tarefas</h3>
            <span class="muted"><?= count($pending) ?> pendente(s)</span>
        </div>
        <form method="post" action="/tasks" class="task-add">
            <?= csrf_field() ?>
            <input type="text" name="title" placeholder="Nova tarefa…" maxlength="200" required>
            <button class="btn btn-primary" aria-label="Adicionar"><i class="bi bi-plus-lg"></i></button>
        </form>
        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <li class="<?= $task['done'] ? 'done' : '' ?>">
                    <form method="post" action="/tasks/<?= (int) $task['id'] ?>/toggle">
                        <?= csrf_field() ?>
                        <button class="check" aria-label="Concluir">
                            <i class="bi <?= $task['done'] ? 'bi-check-circle-fill' : 'bi-circle' ?>"></i>
                        </button>
                    </form>
                    <span class="task-title"><?= e($task['title']) ?></span>
                    <form method="post" action="/tasks/<?= (int) $task['id'] ?>/delete">
                        <?= csrf_field() ?>
                        <button class="icon-btn danger" aria-label="Excluir"><i class="bi bi-trash"></i></button>
                    </form>
                </li>
            <?php endforeach; ?>
            <?php if (!$tasks): ?>
                <li class="empty-row">Nenhuma tarefa. Adicione a primeira acima.</li>
            <?php endif; ?>
        </ul>
    </div>
</section>

<section class="card">
    <div class="card-header">
        <h3><i class="bi bi-clock-history"></i> Últimas ordens de serviço</h3>
        <div class="actions">
            <a href="/orders/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Nova OS</a>
            <a href="/clients" class="btn btn-ghost btn-sm">Clientes</a>
            <a href="/services" class="btn btn-ghost btn-sm">Serviços</a>
        </div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>#</th><th>Cliente</th><th>Status</th><th>Data</th><th class="right">Total</th></tr></thead>
            <tbody>
            <?php foreach ($latest as $o): ?>
                <tr class="clickable" onclick="location.href='/orders/<?= (int) $o['id'] ?>'">
                    <td>#<?= (int) $o['id'] ?></td>
                    <td><?= e($o['client_name']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e($statusLabels[$o['status']]) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td class="right"><?= money($o['total']) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$latest): ?>
                <tr><td colspan="5" class="empty-row">Nenhuma OS cadastrada ainda.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const chartData = <?= json_encode($chart, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    new Chart(document.getElementById('flowChart'), {
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    type: 'line',
                    label: 'Clientes atendidos',
                    data: chartData.clients,
                    borderColor: '#1f9d55',
                    backgroundColor: 'rgba(52, 199, 120, 0.15)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 3,
                    pointBackgroundColor: '#1f9d55',
                },
                {
                    type: 'bar',
                    label: 'OS abertas',
                    data: chartData.orders,
                    backgroundColor: 'rgba(134, 239, 172, 0.55)',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef5f0' } },
                x: { grid: { display: false } },
            },
        },
    });
</script>
