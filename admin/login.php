<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();

if (user_count() === 0) {
    header('Location: setup.php');
    exit;
}

if (current_user()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!login_user($username, $password)) {
        $error = 'Usuário ou senha inválidos.';
    } else {
        header('Location: index.php');
        exit;
    }
}

admin_header('Login');
?>
      <header class="page-header" style="padding-top:1rem;">
        <p class="eyebrow">Painel</p>
        <h1 class="font-display">Entrar</h1>
        <p>Área restrita da Xhybrid.</p>
      </header>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="username">Usuário</label>
          <input id="username" name="username" class="form-input" required autocomplete="username">
        </div>
        <div class="form-group">
          <label for="password">Senha</label>
          <input id="password" name="password" type="password" class="form-input" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Entrar</button>
      </form>
<?php
admin_footer();
