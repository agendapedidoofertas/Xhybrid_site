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

function login_rate_limited(string $ip): bool
{
    $rateDir = dirname(__DIR__) . '/data/rate';
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }
    $rateFile = $rateDir . '/login-' . hash('sha256', $ip) . '.json';
    $now = time();
    $hits = [];
    if (is_file($rateFile)) {
        $prev = json_decode((string) file_get_contents($rateFile), true);
        if (is_array($prev)) {
            $hits = array_values(array_filter($prev, static fn ($t) => is_int($t) && ($now - $t) < 900));
        }
    }
    if (count($hits) >= 12) {
        return true;
    }
    return false;
}

function login_rate_hit(string $ip): void
{
    $rateDir = dirname(__DIR__) . '/data/rate';
    if (!is_dir($rateDir)) {
        @mkdir($rateDir, 0755, true);
    }
    $rateFile = $rateDir . '/login-' . hash('sha256', $ip) . '.json';
    $now = time();
    $hits = [];
    if (is_file($rateFile)) {
        $prev = json_decode((string) file_get_contents($rateFile), true);
        if (is_array($prev)) {
            $hits = array_values(array_filter($prev, static fn ($t) => is_int($t) && ($now - $t) < 900));
        }
    }
    $hits[] = $now;
    @file_put_contents($rateFile, json_encode($hits));
}

$error = '';
$info = '';
if (!empty($_GET['expired'])) {
    $info = 'Sua sessão expirou por inatividade. Faça login novamente.';
} elseif (($_GET['error'] ?? '') === 'google_not_configured') {
    $error = 'Login com Google ainda não configurado (chaves não encontradas em data/google_auth.php).';
} elseif (($_GET['error'] ?? '') === 'google_cancelled') {
    $info = 'Autenticação com Google cancelada.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (login_rate_limited($ip)) {
        $error = 'Muitas tentativas. Aguarde alguns minutos.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (!login_user($username, $password)) {
            login_rate_hit($ip);
            $error = 'Usuário ou senha inválidos.';
        } else {
            header('Location: index.php');
            exit;
        }
    }
}

admin_header('Login');
?>
      <header class="page-header" style="padding-top:1rem;">
        <p class="eyebrow">Painel</p>
        <h1 class="font-display">Entrar</h1>
        <p>Área restrita da Xhybrid.</p>
      </header>
      <?php if ($info): ?><p class="admin-flash admin-flash--info"><?= h($info) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;max-width:28rem;">
        <?= csrf_field() ?>
        <div class="form-group">
          <label for="username">Usuário</label>
          <input id="username" name="username" class="form-input" required autocomplete="username">
        </div>
        <div class="form-group">
          <label for="password">Senha</label>
          <span class="pw-field">
            <input id="password" name="password" type="password" class="form-input" required autocomplete="current-password">
            <button type="button" class="pw-toggle" id="pw-toggle" aria-label="Mostrar senha" title="Mostrar senha">
              <svg class="pw-toggle__icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </span>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;width:100%;">Entrar</button>

        <div style="margin-top:1.25rem;display:flex;justify-content:center;font-size:0.9rem;">
          <a href="forgot_password.php" style="color:var(--text-muted);text-decoration:none;">Esqueci minha senha</a>
        </div>

        <div style="margin-top:1.75rem;padding-top:1.5rem;border-top:1px solid rgba(255,255,255,0.1);text-align:center;">
          <a href="google_login.php" class="btn" style="display:inline-flex;align-items:center;justify-content:center;gap:0.75rem;width:100%;background:#ffffff;color:#1f2937;font-weight:600;border-radius:0.5rem;padding:0.75rem;text-decoration:none;box-shadow:0 1px 3px rgba(0,0,0,0.2);">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.18 0 9.99 0 12s.45 3.82 1.25 5.42l4.03-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
            Entrar com Google
          </a>
        </div>
      </form>
      <script>
      (function () {
        var input = document.getElementById('password');
        var btn = document.getElementById('pw-toggle');
        if (!input || !btn) return;
        btn.addEventListener('click', function () {
          var show = input.type === 'password';
          input.type = show ? 'text' : 'password';
          btn.setAttribute('aria-label', show ? 'Ocultar senha' : 'Mostrar senha');
          btn.setAttribute('title', show ? 'Ocultar senha' : 'Mostrar senha');
          btn.classList.toggle('is-on', show);
        });
      })();
      </script>
<?php
admin_footer();
