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
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>
      <form method="post" class="contact-form admin-form" style="margin:2rem auto 0;">
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
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;">Entrar</button>
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
