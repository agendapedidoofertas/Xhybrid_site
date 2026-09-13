<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/security.php';

auth_boot_session();
security_send_headers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    logout_user();
    header('Location: login.php');
    exit;
}

// GET: formulário mínimo (evita logout CSRF via link)
$user = current_user();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sair — Admin</title>
  <link rel="stylesheet" href="../css/styles.css">
  <link rel="stylesheet" href="admin.css">
</head>
<body data-page="admin">
  <main class="container admin-main" style="padding:2rem;">
    <h1 class="font-display">Encerrar sessão</h1>
    <?php if ($user): ?>
      <p>Confirmar saída de <?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?>?</p>
      <form method="post"><?= csrf_field() ?>
        <button type="submit" class="btn btn-primary">Sair</button>
        <a class="btn btn-outline" href="index.php">Cancelar</a>
      </form>
    <?php else: ?>
      <p><a href="login.php">Login</a></p>
    <?php endif; ?>
  </main>
</body>
</html>
