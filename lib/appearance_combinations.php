<?php

declare(strict_types=1);

/**
 * Combinações curadas por nicho (~10 cada).
 * Manter espelho em crm_software/lib/crm/appearance_pools.php.
 *
 * @return array<string, list<array{id:string,look:string,theme:string,font:string,layout:string,media:string,framework_skin:string,bootswatch?:string}>>
 */
function appearance_combinations_by_niche(): array
{
    $eletricista = [
        combo('eletricista-01', 'azure-blast', 'azul', 'geometric', 'frame', 'media-wide', 'none'),
        combo('eletricista-02', 'tech-glass', 'preto', 'tech', 'soft', 'classic', 'tailwind'),
        combo('eletricista-03', 'sharp-saas', 'graphite', 'saas', 'sharp', 'media-wide', 'bootswatch', 'darkly'),
        combo('eletricista-04', 'ash-glass', 'slate', 'saas', 'compact', 'stack-media', 'none'),
        combo('eletricista-05', 'obsidian', 'preto', 'display', 'frame', 'copy-wide', 'tailwind'),
        combo('eletricista-06', 'azure-blast', 'indigo', 'geometric', 'bento', 'classic', 'bulma'),
        combo('eletricista-07', 'sharp-saas', 'azul', 'tech', 'sharp', 'hero-flip', 'none'),
        combo('eletricista-08', 'tech-glass', 'graphite', 'mono', 'soft', 'flip', 'none'),
        combo('eletricista-09', 'navy-depth', 'midnight', 'geometric', 'magazine', 'about-flip', 'tailwind'),
        combo('eletricista-10', 'obsidian', 'slate', 'saas', 'bento', 'stack-copy', 'bootswatch', 'flatly'),
    ];

    $salao = [
        combo('salao-01', 'fire-sunset', 'sunset', 'rounded', 'loft', 'stack-media', 'none'),
        combo('salao-02', 'warm-studio', 'marrom-claro', 'classic', 'loft', 'center', 'none'),
        combo('salao-03', 'ivory-soft', 'cinza', 'soft', 'pill', 'stack-copy', 'bulma'),
        combo('salao-04', 'amber-flare', 'amber', 'saas', 'bento', 'flip', 'none'),
        combo('salao-05', 'crimson-volt', 'vinho', 'display', 'sharp', 'classic', 'none'),
        combo('salao-06', 'ocean-vivid', 'oceano', 'soft', 'editorial', 'flip', 'bootswatch', 'minty'),
        combo('salao-07', 'indigo-flare', 'indigo', 'lexend', 'magazine', 'about-flip', 'none'),
        combo('salao-08', 'teal-rush', 'teal', 'rounded', 'soft', 'classic', 'none'),
        combo('salao-09', 'blood-noir', 'sangue', 'display', 'sharp', 'classic', 'none'),
        combo('salao-10', 'ivory-soft', 'branco', 'soft', 'pill', 'center', 'none'),
    ];

    $limpeza = [
        combo('limpeza-01', 'teal-rush', 'teal', 'tech', 'soft', 'copy-wide', 'none'),
        combo('limpeza-02', 'ocean-vivid', 'oceano', 'soft', 'editorial', 'flip', 'none'),
        combo('limpeza-03', 'azure-blast', 'azul', 'poppins', 'frame', 'media-wide', 'bulma'),
        combo('limpeza-04', 'ivory-soft', 'gelo', 'soft', 'pill', 'stack-copy', 'none'),
        combo('limpeza-05', 'ash-glass', 'slate', 'work', 'compact', 'stack-media', 'none'),
        combo('limpeza-06', 'sharp-saas', 'graphite', 'inter', 'sharp', 'media-wide', 'bootswatch', 'cosmo'),
        combo('limpeza-07', 'warm-studio', 'verde', 'slab', 'loft', 'center', 'none'),
        combo('limpeza-08', 'ocean-vivid', 'menta', 'raleway', 'soft', 'classic', 'none'),
        combo('limpeza-09', 'navy-depth', 'midnight', 'geometric', 'magazine', 'about-flip', 'none'),
        combo('limpeza-10', 'tech-glass', 'cinza', 'display', 'compact', 'media-wide', 'none'),
    ];

    $generic = [
        combo('generic-01', 'ivory-soft', 'cinza', 'soft', 'pill', 'stack-copy', 'tailwind'),
        combo('generic-02', 'editorial', 'branco', 'editorial', 'editorial', 'flip', 'none'),
        combo('generic-03', 'ocean-vivid', 'oceano', 'soft', 'editorial', 'flip', 'none'),
        combo('generic-04', 'teal-rush', 'teal', 'rounded', 'soft', 'classic', 'bulma'),
        combo('generic-05', 'warm-studio', 'marrom-claro', 'classic', 'loft', 'center', 'none'),
        combo('generic-06', 'copper-heat', 'cobre', 'condensed', 'frame', 'classic', 'none'),
        combo('generic-07', 'obsidian', 'preto', 'figtree', 'frame', 'copy-wide', 'bootswatch', 'cyborg'),
        combo('generic-08', 'ash-glass', 'slate', 'work', 'compact', 'stack-media', 'tailwind'),
        combo('generic-09', 'azure-blast', 'azul', 'poppins', 'frame', 'media-wide', 'none'),
        combo('generic-10', 'sharp-saas', 'graphite', 'inter', 'sharp', 'media-wide', 'tailwind'),
    ];

    return [
        'eletricista' => $eletricista,
        'salao' => $salao,
        'limpeza' => $limpeza,
        'clinica' => $generic,
        'advocacia' => $generic,
        'restaurante' => $generic,
        'locacao' => $generic,
        'dentista' => $salao,
        'estetica' => $salao,
        'contador' => $generic,
        'imobiliaria' => $generic,
        'pet' => $generic,
        'academia' => $eletricista,
        'oficina' => $eletricista,
        'arquitetura' => $generic,
        'consultoria' => $generic,
        'xhybrid' => $generic,
    ];
}

/**
 * @return array{id:string,look:string,theme:string,font:string,layout:string,media:string,framework_skin:string,bootswatch?:string}
 */
function combo(
    string $id,
    string $look,
    string $theme,
    string $font,
    string $layout,
    string $media,
    string $skin,
    ?string $bootswatch = null
): array {
    $row = [
        'id' => $id,
        'look' => $look,
        'theme' => $theme,
        'font' => $font,
        'layout' => $layout,
        'media' => $media,
        'framework_skin' => $skin,
    ];
    if ($bootswatch !== null && $skin === 'bootswatch') {
        $row['bootswatch'] = $bootswatch;
    }
    return $row;
}

/**
 * @return list<array{id:string,look:string,theme:string,font:string,layout:string,media:string,framework_skin:string,bootswatch?:string}>
 */
function appearance_combinations_for_niche(string $niche): array
{
    $all = appearance_combinations_by_niche();
    $niche = strtolower(trim($niche));
    if ($niche === '' || !isset($all[$niche])) {
        return $all['eletricista'];
    }
    return $all[$niche];
}

/**
 * @return array{id:string,look:string,theme:string,font:string,layout:string,media:string,framework_skin:string,bootswatch?:string}|null
 */
function appearance_combination_by_id(string $id): ?array
{
    $id = trim($id);
    if ($id === '') {
        return null;
    }
    foreach (appearance_combinations_by_niche() as $list) {
        foreach ($list as $c) {
            if (($c['id'] ?? '') === $id) {
                return $c;
            }
        }
    }
    return null;
}

/**
 * Swatches aproximados para preview no admin (não são CSS vars do site).
 *
 * @return array<string, array{0:string,1:string,2:string}>
 */
function appearance_theme_swatches(): array
{
    return [
        'preto' => ['#0a0a0a', '#e8e8e8', '#a3a3a3'],
        'graphite' => ['#1f2937', '#f3f4f6', '#60a5fa'],
        'slate' => ['#334155', '#f8fafc', '#94a3b8'],
        'midnight' => ['#0f172a', '#e2e8f0', '#38bdf8'],
        'azul' => ['#1e3a8a', '#eff6ff', '#3b82f6'],
        'oceano' => ['#0e7490', '#ecfeff', '#22d3ee'],
        'teal' => ['#0f766e', '#f0fdfa', '#2dd4bf'],
        'verde' => ['#166534', '#f0fdf4', '#22c55e'],
        'vinho' => ['#7f1d1d', '#fef2f2', '#f87171'],
        'sangue' => ['#450a0a', '#fee2e2', '#ef4444'],
        'cobre' => ['#7c2d12', '#fff7ed', '#fb923c'],
        'marrom-claro' => ['#78350f', '#fffbeb', '#d97706'],
        'amber' => ['#92400e', '#fffbeb', '#f59e0b'],
        'sunset' => ['#9a3412', '#fff7ed', '#fb7185'],
        'cinza' => ['#4b5563', '#f9fafb', '#9ca3af'],
        'branco' => ['#fafafa', '#111827', '#6366f1'],
        'gelo' => ['#e0f2fe', '#0c4a6e', '#0284c7'],
        'indigo' => ['#312e81', '#eef2ff', '#818cf8'],
        'lime' => ['#365314', '#f7fee7', '#84cc16'],
        'menta' => ['#064e3b', '#ecfdf5', '#34d399'],
        'peonia' => ['#9d174d', '#fdf2f8', '#f472b6'],
        'berry' => ['#831843', '#fce7f3', '#ec4899'],
    ];
}

/**
 * @return list<string>
 */
function appearance_framework_skin_ids(): array
{
    return ['none', 'bootswatch', 'bulma', 'tailwind'];
}
