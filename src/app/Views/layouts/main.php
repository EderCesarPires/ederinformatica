<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? '') ?> · Eder Informática</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="/dashboard" class="brand">
            <span class="brand-icon"><i class="bi bi-pc-display"></i></span>
            <span>Eder <strong>Informática</strong></span>
        </a>
        <nav class="menu">
            <a href="/dashboard" class="<?= is_active('/dashboard') ?>"><i class="bi bi-grid-1x2"></i> Dashboard</a>
            <a href="/orders" class="<?= is_active('/orders') ?>"><i class="bi bi-clipboard-check"></i> Ordens de Serviço</a>
            <a href="/clients" class="<?= is_active('/clients') ?>"><i class="bi bi-people"></i> Clientes</a>
            <a href="/services" class="<?= is_active('/services') ?>"><i class="bi bi-tools"></i> Serviços</a>
        </nav>
        <div class="sidebar-footer">
            <div class="user-chip">
                <span class="avatar"><?= e(mb_strtoupper(mb_substr($currentUser['name'] ?? '?', 0, 1))) ?></span>
                <div>
                    <strong><?= e($currentUser['name'] ?? '') ?></strong>
                    <small><?= e($currentUser['email'] ?? '') ?></small>
                </div>
            </div>
            <form method="post" action="/logout">
                <?= csrf_field() ?>
                <button class="btn btn-ghost btn-block"><i class="bi bi-box-arrow-right"></i> Sair</button>
            </form>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <button class="menu-toggle" type="button" onclick="document.getElementById('sidebar').classList.toggle('open')" aria-label="Menu">
                <i class="bi bi-list"></i>
            </button>
            <h1><?= e($title ?? '') ?></h1>
        </header>

        <?php require BASE_PATH . '/app/Views/partials/flash.php'; ?>

        <?= $content ?>
    </main>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
