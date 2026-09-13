<?php

declare(strict_types=1);

/**
 * Matriz de permissões (agência): papel × páginas do painel e plano × recursos.
 * Só Admin edita. Admin (role) sempre tem acesso total.
 */

/** @return array<string, array{label:string,group:string}> */
function permissions_page_catalog(): array
{
    return [
        'index' => ['label' => 'Painel', 'group' => 'Acesso'],
        'lead_hub' => ['label' => 'Hub do lead', 'group' => 'Acesso'],
        'lead_site' => ['label' => 'Resumo rápido', 'group' => 'Acesso'],
        'password' => ['label' => 'Senha (própria)', 'group' => 'Acesso'],
        'contact' => ['label' => 'Contato', 'group' => 'Conteúdo'],
        'texts' => ['label' => 'Textos', 'group' => 'Conteúdo'],
        'images' => ['label' => 'Imagens', 'group' => 'Conteúdo'],
        'services' => ['label' => 'Serviços', 'group' => 'Conteúdo'],
        'brand' => ['label' => 'Marca', 'group' => 'Identidade'],
        'appearance' => ['label' => 'Aparência', 'group' => 'Identidade'],
        'preset' => ['label' => 'Preset', 'group' => 'Identidade'],
        'sections' => ['label' => 'Visibilidade', 'group' => 'Identidade'],
        'leads' => ['label' => 'Leads', 'group' => 'Agência'],
        'plan' => ['label' => 'Plano', 'group' => 'Agência'],
        'backup' => ['label' => 'Backup', 'group' => 'Agência'],
        'users' => ['label' => 'Usuários', 'group' => 'Agência'],
    ];
}

/** @return array<string, array{label:string,group:string}> */
function permissions_plan_feature_catalog(): array
{
    return [
        'brand_edit' => ['label' => 'Cliente pode editar Marca', 'group' => 'Identidade'],
        'appearance' => ['label' => 'Cliente pode editar Aparência', 'group' => 'Identidade'],
        'preset' => ['label' => 'Cliente pode aplicar Preset', 'group' => 'Identidade'],
        'sections' => ['label' => 'Cliente pode editar Visibilidade', 'group' => 'Identidade'],
        'services' => ['label' => 'Cliente pode editar Serviços', 'group' => 'Conteúdo'],
        'images' => ['label' => 'Cliente pode editar Imagens', 'group' => 'Conteúdo'],
        'looks_premium' => ['label' => 'Looks premium liberados', 'group' => 'Site'],
        'animations' => ['label' => 'Animações liberadas', 'group' => 'Site'],
        'login' => ['label' => 'Permite login de cliente', 'group' => 'Acesso'],
    ];
}

/** @return list<string> */
function permissions_editable_roles(): array
{
    return ['editor', 'client_medium', 'client_pro'];
}

/**
 * Defaults atuais do sistema (antes da matriz).
 *
 * @return array<string, list<string>>
 */
function permissions_role_defaults(): array
{
    $all = array_keys(permissions_page_catalog());
    return [
        'admin' => $all,
        'editor' => [
            'index', 'contact', 'texts', 'images', 'leads', 'lead_hub', 'lead_site', 'services', 'password',
        ],
        'client_pro' => [
            'index', 'contact', 'texts', 'images', 'lead_hub', 'lead_site', 'services',
            'password', 'brand', 'sections', 'preset', 'appearance',
        ],
        'client_medium' => [
            'index', 'contact', 'texts', 'images', 'lead_hub', 'lead_site', 'password',
        ],
    ];
}

/**
 * Defaults de recursos por plano.
 *
 * @return array<string, array<string, bool>>
 */
function permissions_plan_feature_defaults(): array
{
    $keys = array_keys(permissions_plan_feature_catalog());
    $empty = array_fill_keys($keys, false);
    return [
        'basic' => array_merge($empty, [
            'login' => false,
            'brand_edit' => false,
            'appearance' => false,
            'preset' => false,
            'sections' => false,
            'services' => false,
            'images' => false,
            'looks_premium' => false,
            'animations' => false,
        ]),
        'medium' => array_merge($empty, [
            'login' => true,
            'brand_edit' => false,
            'appearance' => false,
            'preset' => false,
            'sections' => false,
            'services' => false,
            'images' => true,
            'looks_premium' => false,
            'animations' => true,
        ]),
        'pro' => array_merge($empty, [
            'login' => true,
            'brand_edit' => true,
            'appearance' => true,
            'preset' => true,
            'sections' => true,
            'services' => true,
            'images' => true,
            'looks_premium' => true,
            'animations' => true,
        ]),
    ];
}

function permissions_setting_key_role(string $role): string
{
    return 'perm_pages_' . $role;
}

function permissions_setting_key_plan(string $plan): string
{
    return 'perm_plan_' . plan_normalize($plan);
}

function permissions_raw_get(PDO $pdo, string $key): ?string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute([':k' => $key]);
    $v = $stmt->fetchColumn();
    return $v === false ? null : (string) $v;
}

function permissions_raw_set(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, value, updated_at) VALUES (:k, :v, :t)
         ON CONFLICT(setting_key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
    );
    $stmt->execute([':k' => $key, ':v' => $value, ':t' => gmdate('c')]);
}

/**
 * @return list<string>
 */
function permissions_role_pages(PDO $pdo, string $role): array
{
    $role = strtolower(trim($role));
    $defaults = permissions_role_defaults();
    if ($role === 'admin') {
        return $defaults['admin'];
    }
    if (!isset($defaults[$role])) {
        return ['password'];
    }
    $raw = permissions_raw_get($pdo, permissions_setting_key_role($role));
    if ($raw === null || $raw === '') {
        return $defaults[$role];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $defaults[$role];
    }
    $catalog = array_keys(permissions_page_catalog());
    $out = [];
    foreach ($decoded as $page) {
        if (is_string($page) && in_array($page, $catalog, true)) {
            $out[] = $page;
        }
    }
    // Sempre permitir trocar senha se a lista ficou vazia por engano
    if ($out === []) {
        $out = ['password'];
    }
    return array_values(array_unique($out));
}

/**
 * @param list<string> $pages
 */
function permissions_save_role_pages(PDO $pdo, string $role, array $pages): void
{
    $role = strtolower(trim($role));
    if (!in_array($role, permissions_editable_roles(), true)) {
        throw new InvalidArgumentException('Papel não editável: ' . $role);
    }
    $catalog = array_keys(permissions_page_catalog());
    $clean = [];
    foreach ($pages as $page) {
        if (is_string($page) && in_array($page, $catalog, true)) {
            $clean[] = $page;
        }
    }
    // Segurança: editor/cliente nunca gerenciam usuários via matriz acidental sem querer — admin decide.
    // Backup/users podem ser marcados para editor se o admin quiser.
    if ($clean === []) {
        $clean = ['password'];
    }
    permissions_raw_set(
        $pdo,
        permissions_setting_key_role($role),
        json_encode(array_values(array_unique($clean)), JSON_UNESCAPED_UNICODE) ?: '[]'
    );
}

/**
 * @return array<string, bool>
 */
function permissions_plan_features(PDO $pdo, string $plan): array
{
    require_once __DIR__ . '/plans.php';
    $plan = plan_normalize($plan);
    $defaults = permissions_plan_feature_defaults();
    $base = $defaults[$plan] ?? $defaults['basic'];
    $raw = permissions_raw_get($pdo, permissions_setting_key_plan($plan));
    if ($raw === null || $raw === '') {
        return $base;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        return $base;
    }
    $out = $base;
    foreach (array_keys(permissions_plan_feature_catalog()) as $feat) {
        if (array_key_exists($feat, $decoded)) {
            $out[$feat] = (bool) $decoded[$feat] || $decoded[$feat] === 1 || $decoded[$feat] === '1';
        }
    }
    return $out;
}

/**
 * @param array<string, bool|int|string> $features
 */
function permissions_save_plan_features(PDO $pdo, string $plan, array $features): void
{
    require_once __DIR__ . '/plans.php';
    $plan = plan_normalize($plan);
    $out = [];
    foreach (array_keys(permissions_plan_feature_catalog()) as $feat) {
        $v = $features[$feat] ?? false;
        $out[$feat] = (bool) $v || $v === 1 || $v === '1';
    }
    // Basic nunca libera login de cliente
    if ($plan === 'basic') {
        $out['login'] = false;
    }
    permissions_raw_set(
        $pdo,
        permissions_setting_key_plan($plan),
        json_encode($out, JSON_UNESCAPED_UNICODE) ?: '{}'
    );
}

function permissions_plan_allows(PDO $pdo, string $plan, string $feature): bool
{
    $feats = permissions_plan_features($pdo, $plan);
    return !empty($feats[$feature]);
}

/**
 * Páginas efetivas do usuário (role matrix ∩ filtros de plano para clientes).
 *
 * @return list<string>
 */
function permissions_effective_pages(?array $user, ?string $planTier = null): array
{
    if ($user === null) {
        return [];
    }
    $role = (string) ($user['role'] ?? '');
    if ($role === 'admin') {
        return permissions_role_defaults()['admin'];
    }

    require_once __DIR__ . '/db.php';
    $pdo = db();
    $pages = permissions_role_pages($pdo, $role);

    if ($role !== 'client_medium' && $role !== 'client_pro') {
        return $pages;
    }

    $plan = $planTier;
    if ($plan === null || $plan === '') {
        $leadId = isset($user['crm_lead_id']) ? (int) $user['crm_lead_id'] : 0;
        if ($leadId > 0) {
            require_once __DIR__ . '/published_sites.php';
            require_once __DIR__ . '/plans.php';
            $row = published_site_get_by_lead($pdo, $leadId);
            $plan = plan_normalize((string) ($row['plan_tier'] ?? 'basic'));
        } else {
            $plan = 'basic';
        }
    } else {
        require_once __DIR__ . '/plans.php';
        $plan = plan_normalize($plan);
    }

    $feats = permissions_plan_features($pdo, $plan);
    if (empty($feats['login'])) {
        return ['password'];
    }

    $map = [
        'brand' => 'brand_edit',
        'appearance' => 'appearance',
        'preset' => 'preset',
        'sections' => 'sections',
        'services' => 'services',
        'images' => 'images',
    ];
    $filtered = [];
    foreach ($pages as $page) {
        if (isset($map[$page]) && empty($feats[$map[$page]])) {
            continue;
        }
        $filtered[] = $page;
    }
    return $filtered === [] ? ['password'] : $filtered;
}

function permissions_reset_defaults(PDO $pdo): void
{
    foreach (permissions_editable_roles() as $role) {
        $defaults = permissions_role_defaults()[$role] ?? ['password'];
        permissions_save_role_pages($pdo, $role, $defaults);
    }
    foreach (['basic', 'medium', 'pro'] as $plan) {
        permissions_save_plan_features($pdo, $plan, permissions_plan_feature_defaults()[$plan]);
    }
}
