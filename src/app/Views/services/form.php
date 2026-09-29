<?php
$action = $service ? '/services/' . (int) $service['id'] : '/services';
$value = fn (string $key) => old($old, $key, $service[$key] ?? '');
?>
<div class="card narrow">
    <form method="post" action="<?= $action ?>" class="form">
        <?= csrf_field() ?>
        <div class="field">
            <label for="name">Nome do serviço *</label>
            <input type="text" id="name" name="name" value="<?= $value('name') ?>" required maxlength="150" autofocus>
        </div>
        <div class="field">
            <label for="description">Descrição</label>
            <input type="text" id="description" name="description" value="<?= $value('description') ?>" maxlength="255">
        </div>
        <div class="field">
            <label for="price">Valor (R$) *</label>
            <input type="number" id="price" name="price" value="<?= $value('price') ?>" min="0" step="0.01" required>
        </div>
        <div class="form-actions">
            <a href="/services" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        </div>
    </form>
</div>
