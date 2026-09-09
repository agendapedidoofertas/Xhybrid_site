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

$catalog = [
    'xhybrid' => [
        'title' => 'Xhybrid (agência)',
        'desc' => 'Look Signature (preto + glass), textos de sites/tecnologia e serviços de criação.',
        'agency' => true,
    ],
    'eletricista' => [
        'title' => 'Eletricista',
        'desc' => 'Azul elétrico, serviços elétricos, urgência 24h, área, depoimentos e FAQ.',
    ],
    'clinica' => [
        'title' => 'Clínica',
        'desc' => 'Look oceano, agendamento, especialidades e tom acolhedor.',
    ],
    'restaurante' => [
        'title' => 'Restaurante',
        'desc' => 'Look sunset, cardápio, reservas e delivery.',
    ],
    'advocacia' => [
        'title' => 'Advocacia',
        'desc' => 'Look navy, áreas jurídicas, consultoria e contencioso.',
    ],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $preset = (string) ($_POST['preset'] ?? '');
    $isAgency = !empty($catalog[$preset]['agency']);
    if (!$isAgency && !$presetAllowed) {
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
    $label = $catalog[$ok]['title'] ?? $ok;
    $flash = 'Preset “' . $label . '” aplicado. Ajuste marca, WhatsApp e fotos.';
}

admin_header('Preset', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow">Admin</p>
        <h1 class="font-display">Presets</h1>
        <p>Aplica look, textos, seções e serviços. Xhybrid é a vitrine; os demais aceleram sites de cliente.</p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <?php foreach ($catalog as $id => $meta): ?>
        <section class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
          <h2 class="font-display" style="font-size:1.25rem;margin:0 0 0.5rem;"><?= h($meta['title']) ?></h2>
          <p class="text-muted" style="margin:0 0 1rem;"><?= h($meta['desc']) ?></p>
          <form method="post" onsubmit="return confirm('Isso substitui textos, seções, look e serviços atuais. Continuar?');">
            <?= csrf_field() ?>
            <input type="hidden" name="preset" value="<?= h($id) ?>">
            <button type="submit" class="btn btn-primary" <?= (!empty($meta['agency']) || $presetAllowed) ? '' : 'disabled' ?>>
              Aplicar preset <?= h($meta['title']) ?>
            </button>
          </form>
          <?php if (empty($meta['agency']) && !$presetAllowed): ?>
            <p class="text-muted" style="margin-top:0.75rem;">Bloqueado pelo plano. Libere em <a href="plan.php">Plano</a>.</p>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
<?php
admin_footer();
