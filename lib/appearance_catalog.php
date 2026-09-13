<?php

declare(strict_types=1);

/**
 * Catálogo de temas e fontes de produção.
 *
 * Fontes novas (Montserrat, Raleway, Work Sans, Poppins, Roboto Slab,
 * Libre Baskerville, Cormorant Garamond, Fira Sans, Figtree, Lexend)
 * estão no snippet admin_fonts_stylesheet_href() — espelhar no head
 * das páginas públicas (index.html etc.) quando forem usadas no front.
 */

/**
 * @return array<string, array{label: string, family?: string}>
 */
function appearance_themes(): array
{
    return [
        'preto' => ['label' => 'Preto'],
        'graphite' => ['label' => 'Graphite'],
        'slate' => ['label' => 'Slate'],
        'midnight' => ['label' => 'Midnight'],
        'azul' => ['label' => 'Azul'],
        'oceano' => ['label' => 'Oceano'],
        'teal' => ['label' => 'Teal'],
        'verde' => ['label' => 'Verde'],
        'vinho' => ['label' => 'Vinho'],
        'sangue' => ['label' => 'Sangue'],
        'cobre' => ['label' => 'Cobre'],
        'marrom-claro' => ['label' => 'Marrom claro'],
        'amber' => ['label' => 'Amber'],
        'sunset' => ['label' => 'Sunset'],
        'cinza' => ['label' => 'Cinza'],
        'branco' => ['label' => 'Branco'],
        'gelo' => ['label' => 'Gelo'],
        'indigo' => ['label' => 'Indigo'],
        'lime' => ['label' => 'Lime'],
        'menta' => ['label' => 'Menta'],
    ];
}

/**
 * @return array<string, array{label: string, display: string, body: string}>
 */
function appearance_fonts(): array
{
    return [
        'tech' => ['label' => 'Tech', 'display' => 'Space Grotesk', 'body' => 'IBM Plex Sans'],
        'soft' => ['label' => 'Soft', 'display' => 'Outfit', 'body' => 'Sora'],
        'editorial' => ['label' => 'Editorial', 'display' => 'Fraunces', 'body' => 'Source Sans 3'],
        'saas' => ['label' => 'SaaS', 'display' => 'Plus Jakarta Sans', 'body' => 'Manrope'],
        'mono' => ['label' => 'Mono', 'display' => 'Space Grotesk', 'body' => 'JetBrains Mono'],
        'display' => ['label' => 'Display', 'display' => 'Syne', 'body' => 'DM Sans'],
        'geometric' => ['label' => 'Geometric', 'display' => 'Archivo', 'body' => 'Public Sans'],
        'classic' => ['label' => 'Classic', 'display' => 'Playfair Display', 'body' => 'Lato'],
        'rounded' => ['label' => 'Rounded', 'display' => 'Nunito', 'body' => 'Nunito Sans'],
        'condensed' => ['label' => 'Condensed', 'display' => 'Barlow Condensed', 'body' => 'Barlow'],
        'inter' => ['label' => 'Inter', 'display' => 'Inter', 'body' => 'Inter'],
        'montserrat' => ['label' => 'Montserrat', 'display' => 'Montserrat', 'body' => 'Work Sans'],
        'raleway' => ['label' => 'Raleway', 'display' => 'Raleway', 'body' => 'Raleway'],
        'poppins' => ['label' => 'Poppins', 'display' => 'Poppins', 'body' => 'Poppins'],
        'slab' => ['label' => 'Slab', 'display' => 'Roboto Slab', 'body' => 'Fira Sans'],
        'baskerville' => ['label' => 'Baskerville', 'display' => 'Libre Baskerville', 'body' => 'Source Sans 3'],
        'garamond' => ['label' => 'Garamond', 'display' => 'Cormorant Garamond', 'body' => 'Lato'],
        'figtree' => ['label' => 'Figtree', 'display' => 'Figtree', 'body' => 'Figtree'],
        'lexend' => ['label' => 'Lexend', 'display' => 'Lexend', 'body' => 'Lexend'],
        'work' => ['label' => 'Work Sans', 'display' => 'Work Sans', 'body' => 'Work Sans'],
    ];
}

function appearance_theme_alias(string $id): string
{
    $id = strtolower(trim($id));
    $aliases = [
        'peonia' => 'sunset',
        'lavanda' => 'indigo',
        'neon-roxo' => 'indigo',
        'fuchsia-night' => 'vinho',
        'berry' => 'vinho',
        'neon' => 'lime',
        'violeta' => 'indigo',
        'ameixa' => 'vinho',
        'rosa' => 'sunset',
        'marrom-escuro' => 'marrom-claro',
        'mostarda' => 'amber',
    ];
    if (isset($aliases[$id])) {
        return $aliases[$id];
    }
    $themes = appearance_themes();
    return isset($themes[$id]) ? $id : 'preto';
}

function appearance_font_alias(string $id): string
{
    $id = strtolower(trim($id));
    $fonts = appearance_fonts();
    return isset($fonts[$id]) ? $id : 'tech';
}

/**
 * @return list<string>
 */
function appearance_theme_ids(): array
{
    return array_keys(appearance_themes());
}

/**
 * @return list<string>
 */
function appearance_font_ids(): array
{
    return array_keys(appearance_fonts());
}
