<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/invite.php';

auth_boot_session();
$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$done = false;

if ($token === '') {
    http_response_code(400);
    $error = 'Token ausente.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === '') {
    csrf_verify();
    try {
        invite_consume(db(), $token, trim((string) ($_POST['username'] ?? '')), (string) ($_POST['password'] ?? ''));
        $done = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

admin_header('Convite');
?>
<header class="page-header" style="padding-top:1rem;">
  <p class="eyebrow">Acesso cliente</p>
  <h1 class="font-display"><?= $done ? 'Conta criada' : 'Aceitar convite' ?></h1>
</header>
<?php if ($done): ?>
<p class="admin-flash">Usuário criado. <a href="login.php">Fazer login</a></p>
<?php else: ?>
<?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
<?php if ($token !== ''): ?>
<form method="post" class="contact-form admin-form" style="margin:2rem auto 0;">
  <?= csrf_field() ?>
  <input type="hidden" name="token" value="<?= h($token) ?>">
  <div class="form-group">
    <label for="username">Usuário</label>
    <input id="username" name="username" class="form-input" required minlength="3" autocomplete="username">
  </div>
  <div class="form-group">
    <label for="password">Senha</label>
    <input id="password" name="password" type="password" class="form-input" required minlength="8" autocomplete="new-password">
  </div>
  <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Criar acesso</button>
</form>
<?php endif; ?>
<?php endif; ?>
<?php admin_footer();
