<?php
$action = $order ? '/orders/' . (int) $order['id'] : '/orders';
$selectedClient = (int) ($old['client_id'] ?? $order['client_id'] ?? $_GET['client_id'] ?? 0);
$selectedStatus = $old['status'] ?? $order['status'] ?? 'aberta';

// Linhas de itens: dados reenviados (erro de validação) > itens da OS > uma linha vazia
if (isset($old['service_ids'])) {
    $rows = array_map(null, (array) $old['service_ids'], (array) ($old['quantities'] ?? []));
} elseif ($order) {
    $rows = array_map(fn ($i) => [$i['service_id'], $i['quantity']], $order['items']);
} else {
    $rows = [[0, 1]];
}
?>
<?php if (!$clients || !$services): ?>
    <div class="alert alert-error">
        <i class="bi bi-exclamation-triangle"></i>
        <span>Para abrir uma OS é preciso ter pelo menos um <a href="/clients/create">cliente</a> e um <a href="/services/create">serviço</a> cadastrados.</span>
    </div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" class="form order-form" id="orderForm">
    <?= csrf_field() ?>
    <div class="grid-order">
        <div class="card">
            <h3 class="section-title"><i class="bi bi-person"></i> Cliente</h3>
            <div class="grid-2">
                <div class="field">
                    <label for="client_id">Cliente *</label>
                    <select id="client_id" name="client_id" required>
                        <option value="">Selecione…</option>
                        <?php foreach ($clients as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= $selectedClient === (int) $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?> — <?= e(format_document($c['document'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <?php foreach ($statuses as $key => $label): ?>
                            <option value="<?= e($key) ?>" <?= $selectedStatus === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <h3 class="section-title"><i class="bi bi-tools"></i> Serviços</h3>
            <div class="items" id="items">
                <?php foreach ($rows as [$serviceId, $qty]): ?>
                    <div class="item-row">
                        <select name="service_ids[]" class="svc" required>
                            <option value="">Selecione um serviço…</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= (int) $s['id'] ?>" data-price="<?= e($s['price']) ?>" <?= (int) $serviceId === (int) $s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['name']) ?> (<?= money($s['price']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="quantities[]" class="qty" value="<?= max(1, (int) $qty) ?>" min="1" max="999" aria-label="Quantidade">
                        <span class="line-total">R$ 0,00</span>
                        <button type="button" class="icon-btn danger remove-item" title="Remover"><i class="bi bi-x-lg"></i></button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-ghost btn-sm" id="addItem"><i class="bi bi-plus-lg"></i> Adicionar serviço</button>

            <div class="field" style="margin-top:1.25rem">
                <label for="notes">Observações</label>
                <textarea id="notes" name="notes" rows="3" placeholder="Defeito relatado, acessórios entregues…"><?= old($old, 'notes', $order['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <aside class="card summary">
            <h3 class="section-title"><i class="bi bi-receipt"></i> Resumo</h3>
            <div class="grid-2">
                <div class="field">
                    <label for="discount_percent">Desconto (%)</label>
                    <input type="number" id="discount_percent" name="discount_percent" min="0" max="100" step="0.01"
                           value="<?= old($old, 'discount_percent', $order ? (float) $order['discount_percent'] : 0) ?>">
                </div>
                <div class="field">
                    <label for="surcharge_percent">Acréscimo (%)</label>
                    <input type="number" id="surcharge_percent" name="surcharge_percent" min="0" max="100" step="0.01"
                           value="<?= old($old, 'surcharge_percent', $order ? (float) $order['surcharge_percent'] : 0) ?>">
                </div>
            </div>
            <dl class="totals">
                <dt>Subtotal</dt><dd id="sumSubtotal">R$ 0,00</dd>
                <dt>Desconto</dt><dd id="sumDiscount" class="neg">- R$ 0,00</dd>
                <dt>Acréscimo</dt><dd id="sumSurcharge" class="pos">+ R$ 0,00</dd>
                <dt class="grand">Total</dt><dd class="grand" id="sumTotal">R$ 0,00</dd>
            </dl>
            <small class="hint">O desconto é aplicado sobre o subtotal e o acréscimo sobre o valor já com desconto. O valor final é recalculado no servidor.</small>
            <div class="form-actions">
                <a href="<?= $order ? '/orders/' . (int) $order['id'] : '/orders' ?>" class="btn btn-ghost">Cancelar</a>
                <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar OS</button>
            </div>
        </aside>
    </div>
</form>
