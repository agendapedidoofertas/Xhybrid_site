<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/presets.php';

auth_boot_session();
$user = require_role_admin();

$flash = '';
$error = '';
require_once dirname(__DIR__) . '/lib/settings.php';
$settings = settings_all(db());
$presetAllowed = ($settings['feature_preset_nicho'] ?? '1') === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $preset = (string) ($_POST['preset'] ?? '');
    // Xhybrid é a vitrine da agência — sempre liberado para admin
    if ($preset !== 'xhybrid' && !$presetAllowed) {
        $error = 'Preset de nicho não está liberado neste plano. Ative em Plano.';
    } else {
        try {
            preset_apply(db(), $preset);
            header('Location: preset.php?ok=' . rawurlencode($preset));
            exit;
        } catch (Throwable $e) {
            $error = 'Não foi possível aplicar: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['ok'])) {
    $ok = (string) $_GET['ok'];
    if ($ok === 'xhybrid') {
        $flash = 'Preset Xhybrid aplicado — marca, textos e look Signature.';
    } elseif ($ok === 'eletricista') {
        $flash = 'Preset Eletricista aplicado. Ajuste marca, WhatsApp e fotos.';
    } else {
        $flash = 'Preset aplicado.';
    }
}

admin_header('Preset', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Presets</h1>
        <p>Aplica de uma vez look, textos, seções e serviços. Use Xhybrid para a vitrine da agência; Eletricista para sites de cliente.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <section class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <h2 class="font-display" style="font-size:1.25rem;margin:0 0 0.5rem;">Xhybrid (agência)</h2>
        <p class="text-muted" style="margin:0 0 1rem;">Look Signature (preto + glass transparente), textos de sites/tecnologia e serviços de criação.</p>
        <form method="post" onsubmit="return confirm('Isso substitui textos, seções, look e serviços atuais pela vitrine Xhybrid. Continuar?');">
          <?= csrf_field() ?>
          <input type="hidden" name="preset" value="xhybrid">
          <button type="submit" class="btn btn-primary">Aplicar preset Xhybrid</button>
        </form>
      </section>

      <section class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <h2 class="font-display" style="font-size:1.25rem;margin:0 0 0.5rem;">Eletricista</h2>
        <p class="text-muted" style="margin:0 0 1rem;">Azul elétrico, serviços elétricos, urgência 24h, área, depoimentos e FAQ prontos.</p>
        <form method="post" onsubmit="return confirm('Isso substitui textos, seções, look e serviços atuais. Continuar?');">
          <?= csrf_field() ?>
          <input type="hidden" name="preset" value="eletricista">
          <button type="submit" class="btn btn-primary" <?= $presetAllowed ? '' : 'disabled' ?>>Aplicar preset Eletricista</button>
        </form>
        <?php if (!$presetAllowed): ?>
          <p class="text-muted" style="margin-top:0.75rem;">Bloqueado pelo plano atual. Libere em <a href="plan.php">Plano</a>.</p>
        <?php endif; ?>
      </section>
<?php
admin_footer();
