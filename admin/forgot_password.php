<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';

auth_boot_session();

if (current_user()) {
    header('Location: index.php');
    exit;
}

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $login = trim((string) ($_POST['login'] ?? ''));

    if ($login === '') {
        $error = 'Informe seu usuário ou e-mail.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE username = :l OR LOWER(email) = LOWER(:l)');
        $stmt->execute([':l' => $login]);
        $user = $stmt->fetch();

        // Sempre exibe mensagem amigável para não vazar se o usuário existe ou não
        $msg = 'Se o usuário ou e-mail existir, um link de recuperação foi gerado com validade de 15 minutos.';

        if ($user) {
            $rawToken = bin2hex(random_bytes(24));
            $tokenHash = hash('sha256', $rawToken);
            $expiresAt = gmdate('c', time() + 900); // 15 minutos

            $ins = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at, created_at) VALUES (:uid, :th, :exp, :cr)');
            $ins->execute([
                ':uid' => (int) $user['id'],
                ':th' => $tokenHash,
                ':exp' => $expiresAt,
                ':cr' => gmdate('c'),
            ]);

            // Monta o link de recuperação
            $scheme = request_is_https() ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
            $resetLink = $scheme . '://' . $host . '/admin/reset_password.php?token=' . $rawToken;

            // Envia notificação pelo WhatsApp Bot se configurado
            $crmRoot = dirname(__DIR__, 2) . '/crm_software';
            if (is_file($crmRoot . '/lib/whatsapp/WhatsAppService.php')) {
                require_once $crmRoot . '/lib/whatsapp/WhatsAppService.php';
                $adminPhone = whatsapp_admin_phone();
                if ($adminPhone !== '') {
                    $waMsg = "🔐 *Recuperação de Senha (Xhybrid Admin)*\n\n"
                        . "Uma solicitação de redefinição de senha foi feita para o usuário *{$user['username']}*.\n\n"
                        . "Acesse o link abaixo em até 15 minutos para criar uma nova senha:\n"
                        . "{$resetLink}\n\n"
                        . "Se você não solicitou, ignore esta mensagem.";
                    whatsapp_send($adminPhone, $waMsg);
                }
            }
        }
    }
}

admin_header('Recuperar Senha');
?>
      <header class="page-header" style="padding-top:1rem;">
        <p class="eyebrow">Segurança</p>
        <h1 class="font-display">Recuperar Senha</h1>
        <p>Informe seu usuário ou e-mail cadastrado.</p>
      </header>

      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <?php if ($msg): ?><p class="admin-flash admin-flash--success"><?= h($msg) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;max-width:28rem;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="login">Usuário ou E-mail</label>
          <input id="login" name="login" class="form-input" required autocomplete="username" autofocus>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;width:100%;">Gerar link de recuperação</button>
        <p style="text-align:center;margin-top:1.5rem;font-size:0.9rem;">
          <a href="login.php" style="color:var(--text-muted);text-decoration:none;">← Voltar para o Login</a>
        </p>
      </form>
<?php
admin_footer();
