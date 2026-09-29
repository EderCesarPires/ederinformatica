<?php foreach ($flash ?? [] as $msg): ?>
    <div class="alert alert-<?= e($msg['type']) ?>">
        <i class="bi <?= $msg['type'] === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?>"></i>
        <span><?= e($msg['message']) ?></span>
        <button type="button" class="alert-close" onclick="this.parentElement.remove()" aria-label="Fechar">&times;</button>
    </div>
<?php endforeach; ?>
