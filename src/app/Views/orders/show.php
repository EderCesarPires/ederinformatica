<?php
$afterDiscount = $order['subtotal'] * (1 - $order['discount_percent'] / 100);
$discountValue = $order['subtotal'] - $afterDiscount;
$surchargeValue = $afterDiscount * $order['surcharge_percent'] / 100;
?>
<div class="toolbar">
    <a href="/orders" class="btn btn-ghost"><i class="bi bi-arrow-left"></i> Voltar</a>
    <div class="actions">
        <button type="button" class="btn btn-ghost" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
        <a href="/orders/<?= (int) $order['id'] ?>/edit" class="btn btn-primary"><i class="bi bi-pencil"></i> Editar</a>
        <form method="post" action="/orders/<?= (int) $order['id'] ?>/delete" data-confirm="Excluir a OS #<?= (int) $order['id'] ?>?">
            <?= csrf_field() ?>
            <button class="btn btn-danger"><i class="bi bi-trash"></i> Excluir</button>
        </form>
    </div>
</div>

<div class="card os-sheet">
    <div class="os-head">
        <div>
            <h2>Ordem de Serviço #<?= (int) $order['id'] ?></h2>
            <span class="muted">Aberta em <?= date('d/m/Y \à\s H:i', strtotime($order['created_at'])) ?> por <?= e($order['user_name']) ?></span>
        </div>
        <span class="badge badge-<?= e($order['status']) ?> lg"><?= e($statuses[$order['status']]) ?></span>
    </div>

    <div class="os-client">
        <div><small>Cliente</small><strong><?= e($order['client_name']) ?></strong></div>
        <div><small>Documento</small><strong><?= e(format_document($order['client_document'])) ?></strong></div>
        <div><small>CEP</small><strong><?= e(format_cep($order['client_cep'])) ?></strong></div>
        <div><small>Endereço</small><strong><?= e(trim(($order['client_address'] ?? '') . ' ' . ($order['client_complement'] ?? '')) ?: '—') ?></strong></div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Serviço</th><th class="right">Qtd.</th><th class="right">Valor unit.</th><th class="right">Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($order['items'] as $item): ?>
                <tr>
                    <td><?= e($item['service_name']) ?></td>
                    <td class="right"><?= (int) $item['quantity'] ?></td>
                    <td class="right"><?= money($item['unit_price']) ?></td>
                    <td class="right"><?= money($item['quantity'] * $item['unit_price']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="os-footer">
        <div class="os-notes">
            <small>Observações</small>
            <p><?= nl2br(e($order['notes'] ?: 'Nenhuma observação.')) ?></p>
        </div>
        <dl class="totals">
            <dt>Subtotal</dt><dd><?= money($order['subtotal']) ?></dd>
            <?php if ((float) $order['discount_percent'] > 0): ?>
                <dt>Desconto (<?= (float) $order['discount_percent'] ?>%)</dt><dd class="neg">- <?= money($discountValue) ?></dd>
            <?php endif; ?>
            <?php if ((float) $order['surcharge_percent'] > 0): ?>
                <dt>Acréscimo (<?= (float) $order['surcharge_percent'] ?>%)</dt><dd class="pos">+ <?= money($surchargeValue) ?></dd>
            <?php endif; ?>
            <dt class="grand">Total</dt><dd class="grand"><?= money($order['total']) ?></dd>
        </dl>
    </div>
</div>
