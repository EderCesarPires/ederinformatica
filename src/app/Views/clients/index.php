<div class="card">
    <div class="card-header">
        <form method="get" action="/clients" class="search">
            <i class="bi bi-search"></i>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar por nome ou documento…">
        </form>
        <a href="/clients/create" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Novo cliente</a>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nome</th><th>Documento</th><th>CEP</th><th>Endereço / Complemento</th><th class="right">Ações</th></tr></thead>
            <tbody>
            <?php foreach ($clients as $c): ?>
                <tr>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><?= e(format_document($c['document'])) ?></td>
                    <td><?= e(format_cep($c['cep'])) ?></td>
                    <td class="muted">
                        <?= e($c['address'] ?: '—') ?>
                        <?php if ($c['complement']): ?><br><small><?= e($c['complement']) ?></small><?php endif; ?>
                    </td>
                    <td class="right actions">
                        <a href="/orders/create?client_id=<?= (int) $c['id'] ?>" class="icon-btn" title="Nova OS"><i class="bi bi-clipboard-plus"></i></a>
                        <a href="/clients/<?= (int) $c['id'] ?>/edit" class="icon-btn" title="Editar"><i class="bi bi-pencil"></i></a>
                        <form method="post" action="/clients/<?= (int) $c['id'] ?>/delete" data-confirm="Excluir o cliente &quot;<?= e($c['name']) ?>&quot;?">
                            <?= csrf_field() ?>
                            <button class="icon-btn danger" title="Excluir"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$clients): ?>
                <tr><td colspan="5" class="empty-row">Nenhum cliente encontrado.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
