<div class="card">
    <div class="card-header">
        <form method="get" action="/services" class="search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar serviço…">
        </form>
        <a href="/services/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo serviço</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Serviço</th><th>Descrição</th><th class="right">Valor</th><th class="right">Ações</th></tr></thead>
            <tbody>
            <?php foreach ($services as $s): ?>
                <tr>
                    <td><strong><?= e($s['name']) ?></strong></td>
                    <td class="muted"><?= e($s['description'] ?: '—') ?></td>
                    <td class="right"><?= money($s['price']) ?></td>
                    <td class="right actions">
                        <a href="/services/<?= (int) $s['id'] ?>/edit" class="icon-btn" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="/services/<?= (int) $s['id'] ?>/delete" data-confirm="Excluir o serviço &quot;<?= e($s['name']) ?>&quot;?">
                            <?= csrf_field() ?>
                            <button class="icon-btn danger" title="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$services): ?>
                <tr><td colspan="4" class="empty-row">Nenhum serviço encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
