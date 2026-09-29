<?php
$action = $client ? '/clients/' . (int) $client['id'] : '/clients';
$value = fn (string $key) => old($old, $key, match (true) {
    $client === null         => '',
    $key === 'document'      => format_document($client['document']),
    $key === 'cep'           => format_cep($client['cep']),
    default                  => $client[$key] ?? '',
});
?>
<div class="card narrow">
    <form method="post" action="<?= $action ?>" class="form">
        <?= csrf_field() ?>
        <div class="field">
            <label for="name">Nome *</label>
            <input type="text" id="name" name="name" value="<?= $value('name') ?>" required maxlength="150" autofocus>
        </div>
        <div class="grid-2">
            <div class="field">
                <label for="document">CPF / CNPJ *</label>
                <input type="text" id="document" name="document" value="<?= $value('document') ?>" required data-mask="document" inputmode="numeric" placeholder="000.000.000-00">
            </div>
            <div class="field">
                <label for="cep">CEP *</label>
                <input type="text" id="cep" name="cep" value="<?= $value('cep') ?>" required data-mask="cep" inputmode="numeric" placeholder="00000-000">
                <small class="hint" id="cep-hint">O endereço é preenchido automaticamente.</small>
            </div>
        </div>
        <div class="field">
            <label for="address">Endereço</label>
            <input type="text" id="address" name="address" value="<?= $value('address') ?>" maxlength="255">
        </div>
        <div class="field">
            <label for="complement">Complemento</label>
            <input type="text" id="complement" name="complement" value="<?= $value('complement') ?>" maxlength="150" placeholder="Nº, apto, bloco…">
        </div>
        <div class="form-actions">
            <a href="/clients" class="btn btn-ghost">Cancelar</a>
            <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Salvar</button>
        </div>
    </form>
</div>
