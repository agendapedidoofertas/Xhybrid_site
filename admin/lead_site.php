<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/lib/auth.php';
require_once dirname(__DIR__) . '/lib/csrf.php';
require_once dirname(__DIR__) . '/lib/admin_layout.php';
require_once dirname(__DIR__) . '/lib/db.php';
require_once dirname(__DIR__) . '/lib/published_sites.php';
require_once dirname(__DIR__) . '/lib/crm_bridge.php';
require_once dirname(__DIR__) . '/lib/security.php';

auth_boot_session();
$user = require_admin();

$leadId = (int) ($_GET['lead_id'] ?? $_POST['lead_id'] ?? 0);
require_lead_access($leadId);
$row = published_site_get_by_lead(db(), $leadId);
if (!$row) {
    http_response_code(404);
    admin_header('Lead não encontrado', $user);
    echo '<p class="admin-flash admin-flash--error">Site do lead #' . (int) $leadId . ' não encontrado. <a href="leads.php">Voltar</a></p>';
    admin_footer();
    exit;
}

$lookBundles = [
    'xhybrid-signature' => ['theme' => 'preto', 'font' => 'saas', 'layout' => 'soft', 'media' => 'classic'],
    'tech-glass' => ['theme' => 'preto', 'font' => 'tech', 'layout' => 'soft', 'media' => 'classic'],
    'editorial' => ['theme' => 'branco', 'font' => 'editorial', 'layout' => 'editorial', 'media' => 'flip'],
    'sharp-saas' => ['theme' => 'graphite', 'font' => 'saas', 'layout' => 'sharp', 'media' => 'media-wide'],
    'warm-studio' => ['theme' => 'marrom-claro', 'font' => 'classic', 'layout' => 'loft', 'media' => 'center'],
    'neon-night' => ['theme' => 'neon', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'obsidian' => ['theme' => 'preto', 'font' => 'display', 'layout' => 'frame', 'media' => 'copy-wide'],
    'navy-depth' => ['theme' => 'midnight', 'font' => 'geometric', 'layout' => 'magazine', 'media' => 'about-flip'],
    'ash-glass' => ['theme' => 'slate', 'font' => 'saas', 'layout' => 'compact', 'media' => 'stack-media'],
    'ivory-soft' => ['theme' => 'cinza', 'font' => 'soft', 'layout' => 'pill', 'media' => 'stack-copy'],
    'petal-sky' => ['theme' => 'peonia', 'font' => 'rounded', 'layout' => 'bento', 'media' => 'flip'],
    'crimson-volt' => ['theme' => 'vinho', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
    'azure-blast' => ['theme' => 'azul', 'font' => 'geometric', 'layout' => 'frame', 'media' => 'media-wide'],
    'volt-lime' => ['theme' => 'lime', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'amber-flare' => ['theme' => 'amber', 'font' => 'saas', 'layout' => 'bento', 'media' => 'flip'],
    'berry-pop' => ['theme' => 'berry', 'font' => 'rounded', 'layout' => 'pill', 'media' => 'center'],
    'teal-rush' => ['theme' => 'teal', 'font' => 'tech', 'layout' => 'soft', 'media' => 'copy-wide'],
    'indigo-flare' => ['theme' => 'indigo', 'font' => 'display', 'layout' => 'magazine', 'media' => 'about-flip'],
    'fire-sunset' => ['theme' => 'sunset', 'font' => 'condensed', 'layout' => 'loft', 'media' => 'stack-media'],
    'copper-heat' => ['theme' => 'cobre', 'font' => 'classic', 'layout' => 'frame', 'media' => 'classic'],
    'ocean-vivid' => ['theme' => 'oceano', 'font' => 'soft', 'layout' => 'editorial', 'media' => 'flip'],
    'blood-noir' => ['theme' => 'sangue', 'font' => 'display', 'layout' => 'sharp', 'media' => 'classic'],
    'violet-pulse' => ['theme' => 'violeta', 'font' => 'geometric', 'layout' => 'magazine', 'media' => 'about-flip'],
    'neon-orchid' => ['theme' => 'neon-roxo', 'font' => 'mono', 'layout' => 'strip', 'media' => 'hero-flip'],
    'plum-ember' => ['theme' => 'ameixa', 'font' => 'classic', 'layout' => 'frame', 'media' => 'media-wide'],
    'cyber-magenta' => ['theme' => 'fuchsia-night', 'font' => 'saas', 'layout' => 'soft', 'media' => 'copy-wide'],
];

$flash = '';
$error = '';
$crmWarn = '';
$payload = published_site_decode_payload($row);
$overlay = published_site_settings_overlay($row);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $look = (string) ($_POST['site_look'] ?? '');
    if ($look === '' || !isset($lookBundles[$look])) {
        $look = (string) ($row['site_look'] ?? 'xhybrid-signature');
        if (!isset($lookBundles[$look])) {
            $look = 'xhybrid-signature';
        }
    }
    $bundle = $lookBundles[$look];

    $fields = [
        'company_name' => trim((string) ($_POST['company_name'] ?? '')),
        'phone' => trim((string) ($_POST['phone'] ?? '')),
        'whatsapp' => trim((string) ($_POST['whatsapp'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'address_street' => trim((string) ($_POST['address_street'] ?? '')),
        'address_number' => trim((string) ($_POST['address_number'] ?? '')),
        'address_complement' => trim((string) ($_POST['address_complement'] ?? '')),
        'neighborhood' => trim((string) ($_POST['neighborhood'] ?? '')),
        'city' => trim((string) ($_POST['city'] ?? '')),
        'state' => trim((string) ($_POST['state'] ?? '')),
        'postal_code' => trim((string) ($_POST['postal_code'] ?? '')),
        'maps_url' => url_http_only(trim((string) ($_POST['maps_url'] ?? ''))),
        'instagram_url' => url_http_only(trim((string) ($_POST['instagram_url'] ?? ''))),
        'facebook_url' => url_http_only(trim((string) ($_POST['facebook_url'] ?? ''))),
        'opening_hours' => trim((string) ($_POST['opening_hours'] ?? '')),
        'site_look' => $look,
        'site_theme' => $bundle['theme'],
        'site_font' => $bundle['font'],
        'site_layout' => $bundle['layout'],
        'site_media' => $bundle['media'],
        'payload' => [
            'brand_tagline' => trim((string) ($_POST['brand_tagline'] ?? '')),
            'home_hero_title_1' => trim((string) ($_POST['home_hero_title_1'] ?? '')),
            'home_hero_title_2' => trim((string) ($_POST['home_hero_title_2'] ?? '')),
            'home_hero_text' => trim((string) ($_POST['home_hero_text'] ?? '')),
            'whatsapp_message' => trim((string) ($_POST['whatsapp_message'] ?? '')),
            'logo_url' => url_http_only(trim((string) ($_POST['logo_url'] ?? ''))),
            'brand_seo_title' => trim((string) ($_POST['brand_seo_title'] ?? '')),
            'brand_seo_description' => trim((string) ($_POST['brand_seo_description'] ?? '')),
        ],
    ];

    try {
        $row = published_site_update_admin(db(), $leadId, $fields);
        $crmWarn = crm_bridge_push_published($row);
        $q = 'lead_id=' . $leadId . '&ok=1';
        if ($crmWarn !== '') {
            $_SESSION['lead_site_crm_warn'] = $crmWarn;
        }
        header('Location: lead_site.php?' . $q);
        exit;
    } catch (Throwable $e) {
        $error = 'Não foi possível salvar: ' . $e->getMessage();
        $payload = array_merge($payload, $fields['payload']);
        foreach ($fields as $k => $v) {
            if ($k !== 'payload' && is_string($v)) {
                $row[$k] = $v;
            }
        }
    }
}

if (isset($_GET['ok'])) {
    $flash = 'Site do lead salvo.';
    $row = published_site_get_by_lead(db(), $leadId) ?? $row;
    $payload = published_site_decode_payload($row);
    $overlay = published_site_settings_overlay($row);
    if (!empty($_SESSION['lead_site_crm_warn'])) {
        $crmWarn = (string) $_SESSION['lead_site_crm_warn'];
        unset($_SESSION['lead_site_crm_warn']);
    } else {
        $flash .= crm_bridge_configured() ? ' CRM atualizado.' : '';
    }
}

$path = published_site_public_path($row);
$look = (string) ($row['site_look'] ?? '');
if (!isset($lookBundles[$look])) {
    $look = 'xhybrid-signature';
}

$val = static function (array $row, array $payload, array $overlay, string $key, string $fallback = '') : string {
    if (isset($row[$key]) && (string) $row[$key] !== '') {
        return (string) $row[$key];
    }
    if (isset($payload[$key]) && (string) $payload[$key] !== '') {
        return (string) $payload[$key];
    }
    if (isset($overlay[$key])) {
        return (string) $overlay[$key];
    }
    return $fallback;
};

admin_header('Lead #' . $leadId, $user);
?>
      <header class="page-header" style="padding-top:0;text-align:left;margin:0;max-width:none;">
        <p class="eyebrow"><a href="leads.php">← Sites de leads</a></p>
        <h1 class="font-display"><?= h((string) $row['company_name']) ?></h1>
        <p>
          CRM #<?= (int) $leadId ?>
          · <?= (int) ($row['site_active'] ?? 0) === 1 ? 'Ativo' : 'Inativo (404 até reativar no CRM)' ?>
          <?php if ($path !== ''): ?>
            · <a href="<?= h($path) ?>" target="_blank" rel="noopener"><?= h($path) ?></a>
          <?php endif; ?>
        </p>
      </header>

      <?php if ($flash): ?><p class="admin-flash"><?= h($flash) ?></p><?php endif; ?>
      <?php if ($crmWarn): ?><p class="admin-flash admin-flash--error"><?= h($crmWarn) ?></p><?php endif; ?>
      <?php if ($error): ?><p class="admin-flash admin-flash--error"><?= h($error) ?></p><?php endif; ?>

      <form method="post" class="contact-form admin-form admin-form--wide" style="margin-top:1.5rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="lead_id" value="<?= (int) $leadId ?>">

        <h2 class="admin-appearance__label">Contato</h2>
        <?php
        $contactFields = [
            'company_name' => 'Nome da empresa',
            'whatsapp' => 'WhatsApp',
            'phone' => 'Telefone',
            'email' => 'E-mail',
            'address_street' => 'Rua',
            'address_number' => 'Número',
            'address_complement' => 'Complemento',
            'neighborhood' => 'Bairro',
            'city' => 'Cidade',
            'state' => 'UF',
            'postal_code' => 'CEP',
            'maps_url' => 'Google Maps (URL)',
            'instagram_url' => 'Instagram (URL)',
            'facebook_url' => 'Facebook (URL)',
        ];
        foreach ($contactFields as $key => $label):
        ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($label) ?></label>
            <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" value="<?= h((string) ($row[$key] ?? '')) ?>">
          </div>
        <?php endforeach; ?>

        <div class="form-group">
          <label for="opening_hours">Horário (uma linha por dia, como no Maps)</label>
          <textarea id="opening_hours" name="opening_hours" class="form-input" rows="5"><?= h((string) ($row['opening_hours'] ?? '')) ?></textarea>
        </div>

        <h2 class="admin-appearance__label" style="margin-top:1.5rem;">Aparência</h2>
        <div class="form-group">
          <label for="site_look">Look</label>
          <select id="site_look" name="site_look" class="form-input">
            <?php foreach ($lookBundles as $id => $_bundle): ?>
              <option value="<?= h($id) ?>" <?= $look === $id ? 'selected' : '' ?>><?= h($id) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <h2 class="admin-appearance__label" style="margin-top:1.5rem;">Textos do site</h2>
        <?php
        $textFields = [
            'brand_tagline' => 'Slogan',
            'home_hero_title_1' => 'Hero — linha 1',
            'home_hero_title_2' => 'Hero — linha 2',
            'home_hero_text' => 'Hero — texto',
            'whatsapp_message' => 'Mensagem padrão WhatsApp',
            'logo_url' => 'Logo (URL)',
            'brand_seo_title' => 'SEO — título',
            'brand_seo_description' => 'SEO — descrição',
        ];
        foreach ($textFields as $key => $label):
            $isLong = in_array($key, ['home_hero_text', 'brand_seo_description', 'whatsapp_message'], true);
            $value = $val($row, $payload, $overlay, $key);
        ?>
          <div class="form-group">
            <label for="<?= h($key) ?>"><?= h($label) ?></label>
            <?php if ($isLong): ?>
              <textarea id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" rows="3"><?= h($value) ?></textarea>
            <?php else: ?>
              <input id="<?= h($key) ?>" name="<?= h($key) ?>" class="form-input" value="<?= h($value) ?>">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Salvar e sincronizar CRM</button>
      </form>
<?php
admin_footer();
