<div class="card">
    <div class="card-header">
        <div class="tabs">
            <a href="/orders" class="<?= $status === '' ? 'active' : '' ?>">Todas</a>
            <?php foreach ($statuses as $key => $label): ?>
                <a href="/orders?status=<?= e($key) ?>" class="<?= $status === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </div>
        <a href="/orders/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nova OS</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
            <tr><th>#</th><th>Cliente</th><th>Status</th><th>Data</th><th class="right">Desc. / Acrésc.</th><th class="right">Total</th><th class="right">Ações</th></tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td><a href="/orders/<?= (int) $o['id'] ?>"><strong>#<?= (int) $o['id'] ?></strong></a></td>
                    <td><?= e($o['client_name']) ?></td>
                    <td><span class="badge badge-<?= e($o['status']) ?>"><?= e($statuses[$o['status']]) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                    <td class="right muted">
                        <?= (float) $o['discount_percent'] > 0 ? '-' . (float) $o['discount_percent'] . '%' : '—' ?>
                        /
                        <?= (float) $o['surcharge_percent'] > 0 ? '+' . (float) $o['surcharge_percent'] . '%' : '—' ?>
                    </td>
                    <td class="right"><strong><?= money($o['total']) ?></strong></td>
                    <td class="right actions">
                        <a href="/orders/<?= (int) $o['id'] ?>" class="icon-btn" title="Ver"><i class="bi bi-eye"></i></a>
                        <a href="/orders/<?= (int) $o['id'] ?>/edit" class="icon-btn" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="/orders/<?= (int) $o['id'] ?>/delete" data-confirm="Excluir a OS #<?= (int) $o['id'] ?>?">
                            <?= csrf_field() ?>
                            <button class="icon-btn danger" title="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?>
                <tr><td colspan="7" class="empty-row">Nenhuma ordem de serviço encontrada.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
