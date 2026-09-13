<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/presets.php';
require_once dirname(__DIR__) . '/lib/lead_admin.php';
require_once dirname(__DIR__) . '/lib/settings.php';

auth_boot_session();
$user = require_page('preset');

$leadId = lead_admin_request_id();
$leadRow = null;
if ($leadId !== null) {
    require_lead_access($leadId);
    $leadRow = lead_admin_resolve($leadId);
    if (!$leadRow) {
        http_response_code(404);
        admin_header('Lead não encontrado', $user);
        echo '<p class="admin-flash admin-flash--error">Lead não encontrado.</p>';
        admin_footer();
        exit;
    }
    lead_admin_set_context($leadId);
} else {
    $user = require_role_admin();
}

$flash = '';
$error = '';
$settings = $leadRow ? lead_admin_settings($leadRow) : settings_all(db());
$presetAllowed = ($settings['feature_preset_nicho'] ?? '1') === '1';

$catalog = [
    'xhybrid' => [
        'title' => 'Xhybrid (agência)',
        'desc' => 'Look Signature, textos de sites/tecnologia, serviços de criação e fotos tech.',
        'agency' => true,
    ],
    'eletricista' => ['title' => 'Eletricista', 'desc' => 'Serviços elétricos, urgência 24h.'],
    'clinica' => ['title' => 'Clínica', 'desc' => 'Agendamento e especialidades.'],
    'restaurante' => ['title' => 'Restaurante', 'desc' => 'Cardápio e reservas.'],
    'advocacia' => ['title' => 'Advocacia', 'desc' => 'Áreas jurídicas.'],
    'limpeza' => ['title' => 'Limpeza', 'desc' => 'Residencial e comercial.'],
    'locacao' => ['title' => 'Locação de veículos', 'desc' => 'Frota e reservas.'],
    'dentista' => ['title' => 'Dentista', 'desc' => 'Consultas e tratamentos odontológicos.'],
    'estetica' => ['title' => 'Estética', 'desc' => 'Facial e corporal.'],
    'salao' => ['title' => 'Salão / Barbearia', 'desc' => 'Corte, cor e barba.'],
    'contador' => ['title' => 'Contador', 'desc' => 'Fiscal, folha e consultoria.'],
    'imobiliaria' => ['title' => 'Imobiliária', 'desc' => 'Compra, venda e aluguel.'],
    'pet' => ['title' => 'Pet', 'desc' => 'Banho, tosa e veterinário.'],
    'academia' => ['title' => 'Academia', 'desc' => 'Musculação e personal.'],
    'oficina' => ['title' => 'Oficina', 'desc' => 'Mecânica e diagnóstico.'],
    'arquitetura' => ['title' => 'Arquitetura', 'desc' => 'Projetos e interiores.'],
    'consultoria' => ['title' => 'Consultoria', 'desc' => 'Negócios e gestão.'],
];

if ($leadId) {
    unset($catalog['xhybrid']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $preset = (string) ($_POST['preset'] ?? '');
    $isAgency = !empty($catalog[$preset]['agency']);
    if ($leadId && ($preset === 'xhybrid' || $isAgency)) {
        $error = 'Preset da agência não pode ser aplicado a um lead.';
    } elseif (!$isAgency && !$presetAllowed) {
        $error = 'Preset de nicho não está liberado neste plano. Ative em Plano.';
    } else {
        try {
            if ($leadId) {
                lead_admin_apply_preset(db(), $leadId, $preset);
                header('Location: preset.php?' . lead_admin_qs($leadId) . '&ok=' . rawurlencode($preset));
            } else {
                preset_apply(db(), $preset);
                header('Location: preset.php?ok=' . rawurlencode($preset));
            }
            exit;
        } catch (Throwable $e) {
            $error = 'Não foi possível aplicar: ' . $e->getMessage();
        }
    }
}

if (isset($_GET['ok'])) {
    $ok = (string) $_GET['ok'];
    $label = $catalog[$ok]['title'] ?? $ok;
    $flash = 'Preset “' . $label . '” aplicado.';
}

admin_header('Preset', $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><?= $leadId ? 'Lead #' . (int) $leadId : 'Admin' ?></p>
        <h1 class="font-display">Presets</h1>
        <p><?= $leadId
            ? 'Aplica look, textos, serviços e imagens do ramo neste lead (sem preset da agência).'
            : 'Aplica look, textos, seções, serviços e imagens do ramo.' ?></p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <div class="preset-grid">
      <?php foreach ($catalog as $id => $meta): ?>
        <?php
          $hero = '../assets/presets/' . $id . '/hero.jpg';
          $heroFs = dirname(__DIR__) . '/assets/presets/' . $id . '/hero.jpg';
          $hasHero = is_file($heroFs);
        ?>
        <section class="contact-form admin-form admin-form--wide preset-card">
          <?php if ($hasHero): ?>
            <div class="preset-card__media">
              <img src="<?= h($hero) ?>" alt="" width="640" height="360" loading="lazy">
            </div>
          <?php endif; ?>
          <h2 class="font-display" style="font-size:1.25rem;margin:0 0 0.5rem;"><?= h($meta['title']) ?></h2>
          <p class="text-muted" style="margin:0 0 1rem;"><?= h($meta['desc']) ?></p>
          <form method="post" onsubmit="return confirm('Isso substitui textos, seções, look, serviços e imagens. Continuar?');">
            <?= csrf_field() ?>
            <?php if ($leadId): ?><input type="hidden" name="lead_id" value="<?= (int) $leadId ?>"><?php endif; ?>
            <input type="hidden" name="preset" value="<?= h($id) ?>">
            <button type="submit" class="btn btn-primary" <?= (!empty($meta['agency']) || $presetAllowed) ? '' : 'disabled' ?>>
              Aplicar preset <?= h($meta['title']) ?>
            </button>
          </form>
          <?php if (empty($meta['agency']) && !$presetAllowed): ?>
            <p class="text-muted" style="margin-top:0.75rem;">Bloqueado pelo plano.</p>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
      </div>
<?php
admin_footer();
