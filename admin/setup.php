<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();

if (user_count() > 0) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    if ($username === '' || strlen($username) < 3) {
        $error = 'Escolha um usuário com pelo menos 3 caracteres.';
    } elseif (strlen($password) < 8) {
        $error = 'A senha precisa ter pelo menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'A confirmação de senha não confere.';
    } else {
        create_user($username, $password, 'admin');
        login_user($username, $password);
        header('Location: index.php');
        exit;
    }
}

admin_header('Criar administrador');
?>
      <header class="page-header" style="padding-top:1rem;">
        <p class="eyebrow">Primeiro acesso</p>
        <h1 class="font-display">Criar administrador</h1>
        <p>Esta tela só aparece enquanto não existir nenhum usuário. A senha é salva com hash no servidor.</p>
      </header>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="username">Usuário</label>
          <input id="username" name="username" class="form-input" required minlength="3" autocomplete="username">
        </div>
        <div class="form-group">
          <label for="password">Senha</label>
          <input id="password" name="password" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="confirm">Confirmar senha</label>
          <input id="confirm" name="confirm" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Criar e entrar</button>
      </form>
<?php
admin_footer();
