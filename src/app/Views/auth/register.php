<form method="post" action="/register" class="form">
    <?= csrf_field() ?>
    <div class="field">
        <label for="name">Nome</label>
        <input type="text" id="name" name="name" value="<?= old($old, 'name') ?>" required minlength="3" autofocus>
    </div>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old($old, 'email') ?>" required>
    </div>
    <div class="grid-2">
        <div class="field">
            <label for="password">Senha</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        <div class="field">
            <label for="password_confirmation">Confirmar senha</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6">
        </div>
    </div>
    <button class="btn btn-primary btn-block"><i class="bi bi-person-plus"></i> Criar conta</button>
</form>
<p class="auth-switch">Já tem conta? <a href="/login">Entrar</a></p>
