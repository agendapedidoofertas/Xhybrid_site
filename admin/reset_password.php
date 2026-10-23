<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();

$token = trim((string) ($_GET['token'] ?? $_POST['token'] ?? ''));
$error = '';
$success = false;

if ($token === '') {
    header('Location: login.php');
    exit;
}

$tokenHash = hash('sha256', $token);
$pdo = db();
$stmt = $pdo->prepare('SELECT id, user_id, expires_at, used_at FROM password_resets WHERE token_hash = :th LIMIT 1');
$stmt->execute([':th' => $tokenHash]);
$reset = $stmt->fetch();

$now = gmdate('c');
if (!$reset || !empty($reset['used_at']) || $reset['expires_at'] < $now) {
    admin_header('Token Inválido');
    echo '<div style="max-width:28rem;margin:3rem auto;text-align:center;">';
    echo '<p class="admin-flash admin-flash--error">Este link de recuperação é inválido ou já expirou.</p>';
    echo '<a href="forgot_password.php" class="btn btn-primary" style="margin-top:1rem;display:inline-block;">Solicitar novo link</a>';
    echo '</div>';
    admin_footer();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $password = (string) ($_POST['password'] ?? '');
    $confirm = (string) ($_POST['confirm'] ?? '');

    if (strlen($password) < 8) {
        $error = 'A nova senha precisa ter pelo menos 8 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'As senhas não coincidem.';
    } else {
        // Marca token como usado
        $upd = $pdo->prepare("UPDATE password_resets SET used_at = :now WHERE id = :id AND (used_at IS NULL OR used_at = '')");
        $upd->execute([':now' => $now, ':id' => (int) $reset['id']]);

        if ($upd->rowCount() === 1) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $updUser = $pdo->prepare('UPDATE users SET password_hash = :h WHERE id = :uid');
            $updUser->execute([':h' => $newHash, ':uid' => (int) $reset['user_id']]);

            login_user_by_id((int) $reset['user_id']);
            header('Location: index.php?password_reset=1');
            exit;
        } else {
            $error = 'Token já utilizado.';
        }
    }
}

admin_header('Nova Senha');
?>
      <header class="page-header" style="padding-top:1rem;">
        <p class="eyebrow">Segurança</p>
        <h1 class="font-display">Definir Nova Senha</h1>
        <p>Crie uma senha forte com no mínimo 8 caracteres.</p>
      </header>

      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;max-width:28rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= h($token) ?>">
        <div class="form-group">
          <label for="password">Nova Senha</label>
          <input id="password" name="password" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <div class="form-group">
          <label for="confirm">Confirmar Nova Senha</label>
          <input id="confirm" name="confirm" type="password" class="form-input" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;width:100%;">Salvar Nova Senha</button>
      </form>
<?php
admin_footer();
