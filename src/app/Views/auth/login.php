<form method="post" action="/login" class="form">
    <?= csrf_field() ?>
    <div class="field">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" value="<?= old($old, 'email') ?>" required autofocus>
    </div>
    <div class="field">
        <label for="password">Senha</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button class="btn btn-primary btn-block"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
</form>
<p class="auth-switch">Ainda não tem conta? <a href="/register">Cadastre-se</a></p>
