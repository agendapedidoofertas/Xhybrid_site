<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();
$user = require_role_admin();

$error = '';
$ok = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $current = (string) ($_POST['current'] ?? '');
    $next = (string) ($_POST['next'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');
    if (strlen($next) < 8) {
        $error = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif ($next !== $confirm) {
        $error = 'A confirmação não confere.';
    } else {
        $error = change_password((int) $user['id'], $current, $next);
        if ($error === '') {
            $ok = 'Senha atualizada.';
        }
    }
}

admin_header('Alterar senha', $user);
?>
      <header class="page-header" style="padding-top:1rem;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Conta</p>
        <h1 class="font-display">Alterar senha</h1>
      </header>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <?php if ($ok): ?><p class="admin-flash"><?= h($ok) ?></p><?php endif; ?>
      <form method="post" class="contact-form admin-form" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="current">Senha atual</label>
          <input id="current" name="current" type="password" class="form-input" required autocomplete="current-password">
        </div>
        <div class="form-group">
          <label for="next">Nova senha</label>
          <input id="next" name="next" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="confirm">Confirmar nova senha</label>
          <input id="confirm" name="confirm" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Salvar senha</button>
      </form>
<?php
admin_footer();
