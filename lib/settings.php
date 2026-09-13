<?php

declare(strict_types=1);

require_once __DIR__ . '/feat_icons.php';

/**
 * Definições dos textos/canais editáveis do site.
 * max = limite de caracteres (responsividade + segurança).
 */
function settings_definitions(): array
{
    return [
        // Contato / canais
        'whatsapp_number' => [
            'group' => 'contact', 'label' => 'WhatsApp (DDI+DDD+número, só dígitos)', 'max' => 15, 'type' => 'whatsapp',
            'default' => '5511999999999',
        ],
        'whatsapp_message' => [
            'group' => 'contact', 'label' => 'Mensagem padrão do WhatsApp', 'max' => 160, 'type' => 'text',
            'default' => 'Olá! Vim pelo site da Xhybrid e quero um orçamento de site.',
        ],
        'email' => [
            'group' => 'contact', 'label' => 'E-mail', 'max' => 80, 'type' => 'email',
            'default' => 'contato@xhybrid.com.br',
        ],
        'instagram_url' => [
            'group' => 'contact', 'label' => 'URL do Instagram', 'max' => 120, 'type' => 'url',
            'default' => 'https://instagram.com/xhybrid',
        ],
        'instagram_label' => [
            'group' => 'contact', 'label' => 'Texto do card Instagram', 'max' => 80, 'type' => 'text',
            'default' => '@xhybrid — projetos e bastidores',
        ],
        'facebook_url' => [
            'group' => 'contact', 'label' => 'URL do Facebook', 'max' => 120, 'type' => 'url',
            'default' => '',
        ],
        'facebook_label' => [
            'group' => 'contact', 'label' => 'Texto do card Facebook', 'max' => 80, 'type' => 'text',
            'default' => '',
        ],
        'tiktok_url' => [
            'group' => 'contact', 'label' => 'URL do TikTok', 'max' => 120, 'type' => 'url',
            'default' => '',
        ],
        'tiktok_label' => [
            'group' => 'contact', 'label' => 'Texto do card TikTok', 'max' => 80, 'type' => 'text',
            'default' => '',
        ],
        'address' => [
            'group' => 'contact', 'label' => 'Endereço', 'max' => 160, 'type' => 'text',
            'default' => '',
        ],
        'maps_url' => [
            'group' => 'contact', 'label' => 'URL do Google Maps', 'max' => 220, 'type' => 'url',
            'default' => '',
        ],
        'contact_whatsapp_desc' => [
            'group' => 'contact', 'label' => 'Texto do card WhatsApp', 'max' => 80, 'type' => 'text',
            'default' => 'O jeito mais rápido de pedir um orçamento',
        ],
        'contact_response_title' => [
            'group' => 'contact', 'label' => 'Título tempo de resposta', 'max' => 40, 'type' => 'text',
            'default' => 'Tempo de resposta',
        ],
        'contact_response_text' => [
            'group' => 'contact', 'label' => 'Texto tempo de resposta', 'max' => 80, 'type' => 'text',
            'default' => 'Respondemos em até 1 dia útil',
        ],

        // Menu (4 itens fixos — só rótulos)
        'nav_index' => [
            'group' => 'menu', 'label' => 'Menu 1 — Início', 'max' => 16, 'type' => 'short',
            'default' => 'Início',
        ],
        'nav_sobre' => [
            'group' => 'menu', 'label' => 'Menu 2 — Sobre', 'max' => 16, 'type' => 'short',
            'default' => 'Sobre',
        ],
        'nav_galeria' => [
            'group' => 'menu', 'label' => 'Menu 3 — Galeria', 'max' => 16, 'type' => 'short',
            'default' => 'Projetos',
        ],
        'nav_contato' => [
            'group' => 'menu', 'label' => 'Menu 4 — Contato', 'max' => 16, 'type' => 'short',
            'default' => 'Contato',
        ],

        // Rodapé
        'footer_tagline' => [
            'group' => 'footer', 'label' => 'Frase do rodapé', 'max' => 180, 'type' => 'text',
            'default' => 'Agência de criação de sites, manutenção e tecnologia — presença digital profissional para o seu negócio.',
        ],
        'footer_link_sobre' => [
            'group' => 'footer', 'label' => 'Link rodapé — Sobre', 'max' => 24, 'type' => 'short',
            'default' => 'Sobre nós',
        ],
        'fab_label' => [
            'group' => 'footer', 'label' => 'Texto do botão flutuante WhatsApp', 'max' => 24, 'type' => 'short',
            'default' => 'Fale conosco',
        ],
        'footer_hours_title' => [
            'group' => 'footer', 'label' => 'Horário — título', 'max' => 40, 'type' => 'short',
            'default' => 'Horário de funcionamento',
        ],
        'footer_hours_line1' => [
            'group' => 'footer', 'label' => 'Horário — linha 1', 'max' => 80, 'type' => 'text',
            'default' => 'Segunda a sexta: 9h – 18h',
        ],
        'footer_hours_line2' => [
            'group' => 'footer', 'label' => 'Horário — linha 2', 'max' => 80, 'type' => 'text',
            'default' => 'Sábado: 9h – 13h',
        ],
        'footer_hours_line3' => [
            'group' => 'footer', 'label' => 'Horário — linha 3', 'max' => 80, 'type' => 'text',
            'default' => 'Domingo: fechado',
        ],

        // Aparência do site (só admin)
        'appearance_look' => [
            'group' => 'appearance', 'label' => 'Look do site', 'max' => 32, 'type' => 'choice',
            'choices' => [
                'xhybrid-signature', 'obsidian', 'tech-glass', 'ash-glass', 'sharp-saas', 'ivory-soft', 'editorial',
                'navy-depth', 'azure-blast', 'indigo-flare', 'ocean-vivid',
                'neon-night', 'teal-rush',
                'volt-lime',
                'amber-flare', 'copper-heat', 'fire-sunset', 'warm-studio',
                'blood-noir', 'crimson-volt', 'plum-ember',
                'neon-orchid', 'violet-pulse', 'cyber-magenta', 'berry-pop', 'petal-sky',
            ],
            'default' => 'xhybrid-signature',
        ],
        'appearance_theme' => [
            'group' => 'appearance', 'label' => 'Cor / tema', 'max' => 32, 'type' => 'choice',
            'choices' => [
                'marrom-claro', 'marrom-escuro', 'branco', 'preto', 'azul', 'rosa',
                'verde', 'teal', 'amber', 'cinza', 'indigo', 'graphite', 'oceano', 'lime',
                'vinho', 'cobre', 'slate', 'neon', 'berry', 'midnight',
                'menta', 'peonia', 'lavanda', 'mostarda', 'gelo', 'sunset',
                'sangue', 'violeta', 'neon-roxo', 'ameixa', 'fuchsia-night',
            ],
            'default' => 'preto',
        ],
        'appearance_font' => [
            'group' => 'appearance', 'label' => 'Fonte', 'max' => 32, 'type' => 'choice',
            'choices' => [
                'tech', 'soft', 'editorial', 'saas', 'mono', 'display',
                'geometric', 'classic', 'rounded', 'condensed',
                'inter', 'montserrat', 'raleway', 'poppins', 'slab',
                'baskerville', 'garamond', 'figtree', 'lexend', 'work',
            ],
            'default' => 'saas',
        ],
        'appearance_layout' => [
            'group' => 'appearance', 'label' => 'Molde', 'max' => 32, 'type' => 'choice',
            'choices' => [
                'soft', 'sharp', 'bento', 'editorial', 'pill', 'compact',
                'frame', 'magazine', 'loft', 'strip',
            ],
            'default' => 'soft',
        ],
        'appearance_media' => [
            'group' => 'appearance', 'label' => 'Imagens / layout', 'max' => 32, 'type' => 'choice',
            'choices' => [
                'classic', 'flip', 'hero-flip', 'about-flip', 'stack-media', 'stack-copy',
                'center', 'media-wide', 'copy-wide', 'gallery-dense',
            ],
            'default' => 'classic',
        ],
        'appearance_combination_id' => [
            'group' => 'appearance', 'label' => 'Combinação curada', 'max' => 48, 'type' => 'short',
            'default' => '',
        ],
        'framework_skin' => [
            'group' => 'appearance', 'label' => 'Framework skin', 'max' => 24, 'type' => 'choice',
            'choices' => ['none', 'bootswatch', 'bulma', 'tailwind'],
            'default' => 'none',
        ],
        'framework_bootswatch' => [
            'group' => 'appearance', 'label' => 'Tema Bootswatch', 'max' => 24, 'type' => 'short',
            'default' => '',
        ],
        'appearance_locked_by_admin' => [
            'group' => 'appearance', 'label' => 'Aparência travada pelo admin', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'],
            'default' => '0',
        ],

        // Home
        'home_badge' => [
            'group' => 'home', 'label' => 'Badge do hero', 'max' => 40, 'type' => 'short',
            'default' => 'Sites & tecnologia',
        ],
        'home_hero_title_1' => [
            'group' => 'home', 'label' => 'Título hero (linha 1)', 'max' => 40, 'type' => 'short',
            'default' => 'Seu negócio,',
        ],
        'home_hero_title_2' => [
            'group' => 'home', 'label' => 'Título hero (linha 2, itálico)', 'max' => 40, 'type' => 'short',
            'default' => 'online de verdade.',
        ],
        'home_hero_text' => [
            'group' => 'home', 'label' => 'Texto do hero', 'max' => 220, 'type' => 'text',
            'default' => 'Somos a Xhybrid — criação de sites, manutenção e tecnologia para empresas que querem presença digital profissional.',
        ],
        'home_btn_gallery' => [
            'group' => 'home', 'label' => 'Botão galeria', 'max' => 28, 'type' => 'short',
            'default' => 'Ver projetos',
        ],
        'home_btn_quote' => [
            'group' => 'home', 'label' => 'Botão orçamento', 'max' => 28, 'type' => 'short',
            'default' => 'Orçamento',
        ],
        'home_weave_title' => [
            'group' => 'home', 'label' => 'Título “O que fazemos”', 'max' => 40, 'type' => 'short',
            'default' => 'O que fazemos',
        ],
        'home_weave_subtitle' => [
            'group' => 'home', 'label' => 'Subtítulo “O que fazemos”', 'max' => 160, 'type' => 'text',
            'default' => 'Do site institucional à manutenção contínua — tecnologia sob medida para o seu negócio.',
        ],
        'home_feat_1_title' => [
            'group' => 'home', 'label' => 'Card 1 — título', 'max' => 30, 'type' => 'short',
            'default' => 'Criação de sites',
        ],
        'home_feat_1_icon' => [
            'group' => 'home', 'label' => 'Card 1 — ícone', 'max' => 24, 'type' => 'icon',
            'choices' => feat_icon_catalog(), 'default' => 'layout',
        ],
        'home_feat_1_text' => [
            'group' => 'home', 'label' => 'Card 1 — texto', 'max' => 120, 'type' => 'text',
            'default' => 'Landing pages e sites corporativos modernos, rápidos e alinhados à sua marca.',
        ],
        'home_feat_2_title' => [
            'group' => 'home', 'label' => 'Card 2 — título', 'max' => 30, 'type' => 'short',
            'default' => 'Manutenção',
        ],
        'home_feat_2_icon' => [
            'group' => 'home', 'label' => 'Card 2 — ícone', 'max' => 24, 'type' => 'icon',
            'choices' => feat_icon_catalog(), 'default' => 'wrench',
        ],
        'home_feat_2_text' => [
            'group' => 'home', 'label' => 'Card 2 — texto', 'max' => 120, 'type' => 'text',
            'default' => 'Atualizações, backups, performance e correções para o site ficar sempre no ar.',
        ],
        'home_feat_3_title' => [
            'group' => 'home', 'label' => 'Card 3 — título', 'max' => 30, 'type' => 'short',
            'default' => 'Tecnologia',
        ],
        'home_feat_3_icon' => [
            'group' => 'home', 'label' => 'Card 3 — ícone', 'max' => 24, 'type' => 'icon',
            'choices' => feat_icon_catalog(), 'default' => 'cpu',
        ],
        'home_feat_3_text' => [
            'group' => 'home', 'label' => 'Card 3 — texto', 'max' => 120, 'type' => 'text',
            'default' => 'Integrações, automações e melhorias digitais para otimizar o dia a dia.',
        ],
        'home_destaques_title' => [
            'group' => 'home', 'label' => 'Título destaques', 'max' => 40, 'type' => 'short',
            'default' => 'Projetos em destaque',
        ],
        'home_destaques_subtitle' => [
            'group' => 'home', 'label' => 'Subtítulo destaques', 'max' => 120, 'type' => 'text',
            'default' => 'Alguns trabalhos de criação e desenvolvimento.',
        ],
        'home_destaques_link' => [
            'group' => 'home', 'label' => 'Link “ver galeria completa”', 'max' => 40, 'type' => 'short',
            'default' => 'Ver portfólio completo',
        ],
        'home_cta_title_1' => [
            'group' => 'home', 'label' => 'CTA título (parte 1)', 'max' => 40, 'type' => 'short',
            'default' => 'Tem um projeto?',
        ],
        'home_cta_title_2' => [
            'group' => 'home', 'label' => 'CTA título (parte 2, itálico)', 'max' => 40, 'type' => 'short',
            'default' => 'A gente desenvolve.',
        ],
        'home_cta_text' => [
            'group' => 'home', 'label' => 'Texto do CTA', 'max' => 240, 'type' => 'text',
            'default' => 'Conte o que precisa — site novo, manutenção ou melhoria tecnológica — e montamos a melhor proposta.',
        ],
        'home_cta_btn' => [
            'group' => 'home', 'label' => 'Botão do CTA', 'max' => 30, 'type' => 'short',
            'default' => 'Orçamento',
        ],

        // Sobre
        'about_eyebrow' => [
            'group' => 'sobre', 'label' => 'Eyebrow', 'max' => 30, 'type' => 'short',
            'default' => 'Sobre nós',
        ],
        'about_title_1' => [
            'group' => 'sobre', 'label' => 'Título (parte 1)', 'max' => 40, 'type' => 'short',
            'default' => 'Tecnologia,',
        ],
        'about_title_2' => [
            'group' => 'sobre', 'label' => 'Título (parte 2, itálico)', 'max' => 40, 'type' => 'short',
            'default' => 'com clareza.',
        ],
        'about_p1' => [
            'group' => 'sobre', 'label' => 'Parágrafo 1', 'max' => 350, 'type' => 'text',
            'default' => 'A Xhybrid nasceu para ajudar empresas a terem presença digital profissional: sites bem feitos, manutenção confiável e tecnologia aplicada ao negócio.',
        ],
        'about_p2' => [
            'group' => 'sobre', 'label' => 'Parágrafo 2', 'max' => 350, 'type' => 'text',
            'default' => 'Cuidamos do visual, da performance e da operação — do primeiro briefing à publicação, com comunicação direta e prazos claros.',
        ],
        'about_p3' => [
            'group' => 'sobre', 'label' => 'Parágrafo 3', 'max' => 350, 'type' => 'text',
            'default' => 'Mais do que páginas no ar, entregamos uma base digital sólida para você atender clientes, divulgar serviços e crescer online.',
        ],
        'about_btn' => [
            'group' => 'sobre', 'label' => 'Botão', 'max' => 30, 'type' => 'short',
            'default' => 'Ver projetos',
        ],
        'about_stat_1_value' => [
            'group' => 'sobre', 'label' => 'Destaque 1 — valor', 'max' => 20, 'type' => 'short',
            'default' => '100%',
        ],
        'about_stat_1_text' => [
            'group' => 'sobre', 'label' => 'Destaque 1 — texto', 'max' => 80, 'type' => 'text',
            'default' => 'Foco em entrega e resultado',
        ],
        'about_stat_2_value' => [
            'group' => 'sobre', 'label' => 'Destaque 2 — valor', 'max' => 20, 'type' => 'short',
            'default' => 'Ágil',
        ],
        'about_stat_2_text' => [
            'group' => 'sobre', 'label' => 'Destaque 2 — texto', 'max' => 80, 'type' => 'text',
            'default' => 'Processo claro do briefing à publicação',
        ],
        'about_stat_3_value' => [
            'group' => 'sobre', 'label' => 'Destaque 3 — valor', 'max' => 20, 'type' => 'short',
            'default' => 'Sob medida',
        ],
        'about_stat_3_text' => [
            'group' => 'sobre', 'label' => 'Destaque 3 — texto', 'max' => 80, 'type' => 'text',
            'default' => 'Soluções alinhadas ao seu negócio',
        ],

        // Galeria página
        'gallery_eyebrow' => [
            'group' => 'galeria', 'label' => 'Eyebrow', 'max' => 30, 'type' => 'short',
            'default' => 'Portfolio',
        ],
        'gallery_title' => [
            'group' => 'galeria', 'label' => 'Título', 'max' => 30, 'type' => 'short',
            'default' => 'Projetos',
        ],
        'gallery_subtitle' => [
            'group' => 'galeria', 'label' => 'Subtítulo', 'max' => 160, 'type' => 'text',
            'default' => 'Sites e soluções que já entregamos. Toque em uma foto para ampliar.',
        ],

        // Contato página (cabeçalho / formulário)
        'contact_eyebrow' => [
            'group' => 'contato_page', 'label' => 'Eyebrow', 'max' => 30, 'type' => 'short',
            'default' => 'Get in touch',
        ],
        'contact_title' => [
            'group' => 'contato_page', 'label' => 'Título', 'max' => 24, 'type' => 'short',
            'default' => 'Contato',
        ],
        'contact_subtitle' => [
            'group' => 'contato_page', 'label' => 'Subtítulo', 'max' => 140, 'type' => 'text',
            'default' => 'Orçamentos e dúvidas — escolha o canal que preferir.',
        ],
        'contact_form_title' => [
            'group' => 'contato_page', 'label' => 'Título do formulário', 'max' => 40, 'type' => 'short',
            'default' => 'Escreva para nós',
        ],
        'contact_form_intro' => [
            'group' => 'contato_page', 'label' => 'Intro do formulário', 'max' => 140, 'type' => 'text',
            'default' => 'Preencha abaixo e sua mensagem abre direto no seu e-mail.',
        ],
        'contact_form_btn' => [
            'group' => 'contato_page', 'label' => 'Botão enviar', 'max' => 28, 'type' => 'short',
            'default' => 'Enviar mensagem',
        ],

        /* Marca / identidade */
        'brand_name' => [
            'group' => 'brand', 'label' => 'Nome da marca', 'max' => 48, 'type' => 'short',
            'default' => 'Xhybrid',
        ],
        'brand_tagline' => [
            'group' => 'brand', 'label' => 'Slogan curto', 'max' => 80, 'type' => 'short',
            'default' => 'Sites, manutenção e tecnologia',
        ],
        'brand_city' => [
            'group' => 'brand', 'label' => 'Cidade / região', 'max' => 60, 'type' => 'short',
            'default' => '',
        ],
        'brand_seo_title' => [
            'group' => 'brand', 'label' => 'SEO — título da página', 'max' => 70, 'type' => 'short',
            'default' => 'Xhybrid — Criação de sites, manutenção e tecnologia',
        ],
        'brand_seo_description' => [
            'group' => 'brand', 'label' => 'SEO — descrição', 'max' => 160, 'type' => 'text',
            'default' => 'Agência Xhybrid: criação de sites, manutenção e tecnologia para empresas. Orçamento rápido pelo WhatsApp.',
        ],

        /* SEO por página */
        'seo_sobre_title' => [
            'group' => 'seo', 'label' => 'Sobre — título', 'max' => 70, 'type' => 'short',
            'default' => '',
        ],
        'seo_sobre_description' => [
            'group' => 'seo', 'label' => 'Sobre — descrição', 'max' => 160, 'type' => 'text',
            'default' => '',
        ],
        'seo_galeria_title' => [
            'group' => 'seo', 'label' => 'Projetos — título', 'max' => 70, 'type' => 'short',
            'default' => '',
        ],
        'seo_galeria_description' => [
            'group' => 'seo', 'label' => 'Projetos — descrição', 'max' => 160, 'type' => 'text',
            'default' => '',
        ],
        'seo_contato_title' => [
            'group' => 'seo', 'label' => 'Contato — título', 'max' => 70, 'type' => 'short',
            'default' => '',
        ],
        'seo_contato_description' => [
            'group' => 'seo', 'label' => 'Contato — descrição', 'max' => 160, 'type' => 'text',
            'default' => '',
        ],

        /* Analytics */
        'analytics_ga4_id' => [
            'group' => 'analytics', 'label' => 'Google Analytics 4 (G-XXXXXXXX)', 'max' => 24, 'type' => 'short',
            'default' => '',
        ],
        'analytics_meta_pixel_id' => [
            'group' => 'analytics', 'label' => 'Meta Pixel ID', 'max' => 24, 'type' => 'short',
            'default' => '',
        ],

        /* SMTP */
        'smtp_host' => [
            'group' => 'smtp', 'label' => 'SMTP — host', 'max' => 120, 'type' => 'short',
            'default' => '',
        ],
        'smtp_port' => [
            'group' => 'smtp', 'label' => 'SMTP — porta', 'max' => 6, 'type' => 'short',
            'default' => '587',
        ],
        'smtp_user' => [
            'group' => 'smtp', 'label' => 'SMTP — usuário', 'max' => 120, 'type' => 'short',
            'default' => '',
        ],
        'smtp_pass' => [
            'group' => 'smtp', 'label' => 'SMTP — senha', 'max' => 120, 'type' => 'short',
            'default' => '',
        ],
        'smtp_from' => [
            'group' => 'smtp', 'label' => 'SMTP — e-mail remetente', 'max' => 80, 'type' => 'email',
            'default' => '',
        ],
        'smtp_to' => [
            'group' => 'smtp', 'label' => 'SMTP — e-mail destino (vazio = e-mail do site)', 'max' => 80, 'type' => 'email',
            'default' => '',
        ],

        /* Área e urgência */
        'area_text' => [
            'group' => 'brand', 'label' => 'Área de atendimento', 'max' => 200, 'type' => 'text',
            'default' => '',
        ],
        'urgency_enabled' => [
            'group' => 'brand', 'label' => 'Badge de urgência', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '0',
        ],
        'urgency_label' => [
            'group' => 'brand', 'label' => 'Texto do badge (ex.: 24h)', 'max' => 24, 'type' => 'short',
            'default' => '24h',
        ],

        /* Seções on/off */
        'section_hero' => [
            'group' => 'sections', 'label' => 'Hero', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'section_features' => [
            'group' => 'sections', 'label' => 'O que fazemos', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'section_works' => [
            'group' => 'sections', 'label' => 'Destaques / trabalhos', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'section_area' => [
            'group' => 'sections', 'label' => 'Área de atendimento', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '0',
        ],
        'section_testimonials' => [
            'group' => 'sections', 'label' => 'Depoimentos', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '0',
        ],
        'section_faq' => [
            'group' => 'sections', 'label' => 'FAQ', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '0',
        ],
        'section_cta' => [
            'group' => 'sections', 'label' => 'CTA final', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],

        /* Depoimentos */
        'testimonials_title' => [
            'group' => 'testimonials', 'label' => 'Título da seção', 'max' => 40, 'type' => 'short',
            'default' => 'O que dizem os clientes',
        ],
        'testimonial_1_name' => [
            'group' => 'testimonials', 'label' => 'Depoimento 1 — nome', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_1_city' => [
            'group' => 'testimonials', 'label' => 'Depoimento 1 — cidade', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_1_text' => [
            'group' => 'testimonials', 'label' => 'Depoimento 1 — texto', 'max' => 220, 'type' => 'text',
            'default' => '',
        ],
        'testimonial_2_name' => [
            'group' => 'testimonials', 'label' => 'Depoimento 2 — nome', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_2_city' => [
            'group' => 'testimonials', 'label' => 'Depoimento 2 — cidade', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_2_text' => [
            'group' => 'testimonials', 'label' => 'Depoimento 2 — texto', 'max' => 220, 'type' => 'text',
            'default' => '',
        ],
        'testimonial_3_name' => [
            'group' => 'testimonials', 'label' => 'Depoimento 3 — nome', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_3_city' => [
            'group' => 'testimonials', 'label' => 'Depoimento 3 — cidade', 'max' => 40, 'type' => 'short',
            'default' => '',
        ],
        'testimonial_3_text' => [
            'group' => 'testimonials', 'label' => 'Depoimento 3 — texto', 'max' => 220, 'type' => 'text',
            'default' => '',
        ],

        /* FAQ */
        'faq_title' => [
            'group' => 'faq', 'label' => 'Título da seção', 'max' => 40, 'type' => 'short',
            'default' => 'Perguntas frequentes',
        ],
        'faq_1_q' => [
            'group' => 'faq', 'label' => 'FAQ 1 — pergunta', 'max' => 80, 'type' => 'short',
            'default' => '',
        ],
        'faq_1_a' => [
            'group' => 'faq', 'label' => 'FAQ 1 — resposta', 'max' => 280, 'type' => 'text',
            'default' => '',
        ],
        'faq_2_q' => [
            'group' => 'faq', 'label' => 'FAQ 2 — pergunta', 'max' => 80, 'type' => 'short',
            'default' => '',
        ],
        'faq_2_a' => [
            'group' => 'faq', 'label' => 'FAQ 2 — resposta', 'max' => 280, 'type' => 'text',
            'default' => '',
        ],
        'faq_3_q' => [
            'group' => 'faq', 'label' => 'FAQ 3 — pergunta', 'max' => 80, 'type' => 'short',
            'default' => '',
        ],
        'faq_3_a' => [
            'group' => 'faq', 'label' => 'FAQ 3 — resposta', 'max' => 280, 'type' => 'text',
            'default' => '',
        ],
        'faq_4_q' => [
            'group' => 'faq', 'label' => 'FAQ 4 — pergunta', 'max' => 80, 'type' => 'short',
            'default' => '',
        ],
        'faq_4_a' => [
            'group' => 'faq', 'label' => 'FAQ 4 — resposta', 'max' => 280, 'type' => 'text',
            'default' => '',
        ],

        /* Plano comercial */
        'site_plan' => [
            'group' => 'plan', 'label' => 'Plano contratado', 'max' => 24, 'type' => 'choice',
            'choices' => ['basic', 'medium', 'pro'],
            'default' => 'medium',
        ],
        'feature_page_sobre' => [
            'group' => 'plan', 'label' => 'Mostrar página Sobre', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'feature_page_galeria' => [
            'group' => 'plan', 'label' => 'Mostrar página Projetos', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'feature_page_contato' => [
            'group' => 'plan', 'label' => 'Mostrar página Contato', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'feature_animations' => [
            'group' => 'plan', 'label' => 'Animações no site', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'feature_looks_premium' => [
            'group' => 'plan', 'label' => 'Looks premium (neon / cores fortes)', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '0',
        ],
        'feature_preset_nicho' => [
            'group' => 'plan', 'label' => 'Preset de nicho liberado', 'max' => 1, 'type' => 'choice',
            'choices' => ['0', '1'], 'default' => '1',
        ],
        'limit_services' => [
            'group' => 'plan', 'label' => 'Máx. serviços ativos', 'max' => 3, 'type' => 'short',
            'default' => '6',
        ],
        'limit_gallery' => [
            'group' => 'plan', 'label' => 'Máx. imagens na galeria', 'max' => 3, 'type' => 'short',
            'default' => '12',
        ],
    ];
}

function settings_groups(): array
{
    return [
        'contact' => 'Canais de contato',
        'menu' => 'Menu (4 itens)',
        'footer' => 'Rodapé',
        'appearance' => 'Aparência',
        'brand' => 'Marca e região',
        'plan' => 'Plano e limites',
        'sections' => 'Seções do site',
        'home' => 'Home',
        'sobre' => 'Sobre',
        'galeria' => 'Galeria',
        'contato_page' => 'Página Contato',
        'testimonials' => 'Depoimentos',
        'faq' => 'FAQ',
    ];
}

function settings_strip_controls(string $value): string
{
    $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    return is_string($clean) ? $clean : '';
}

function settings_sanitize(string $key, string $value): string
{
    $defs = settings_definitions();
    if (!isset($defs[$key])) {
        return '';
    }
    $def = $defs[$key];
    $value = settings_strip_controls($value);
    $value = trim(str_replace("\r\n", "\n", $value));
    $type = $def['type'] ?? 'text';
    $max = (int) $def['max'];

    if ($type === 'whatsapp') {
        $value = preg_replace('/\D+/', '', $value) ?? '';
    } elseif ($type === 'email') {
        $value = filter_var($value, FILTER_SANITIZE_EMAIL) ?: '';
        $value = str_replace(['<', '>'], '', $value);
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $value = '';
        }
    } elseif ($type === 'url') {
        require_once __DIR__ . '/security.php';
        $value = url_http_only($value);
    } elseif ($type === 'choice' || $type === 'icon') {
        $choices = $def['choices'] ?? [];
        if (!is_array($choices) || $choices === []) {
            $value = (string) $def['default'];
        } else {
            $allowed = array_is_list($choices) ? $choices : array_keys($choices);
            if (!in_array($value, $allowed, true)) {
                $value = (string) $def['default'];
            }
        }
    } else {
        // Textos: sem HTML
        $value = str_replace(['<', '>'], '', $value);
    }

    if (function_exists('mb_substr')) {
        $value = mb_substr($value, 0, $max, 'UTF-8');
    } else {
        $value = substr($value, 0, $max);
    }

    return $value;
}

function settings_all(PDO $pdo): array
{
    $defs = settings_definitions();
    $out = [];
    foreach ($defs as $key => $def) {
        $out[$key] = (string) $def['default'];
    }

    $stmt = $pdo->query('SELECT setting_key, value FROM settings');
    foreach ($stmt->fetchAll() as $row) {
        $key = (string) ($row['setting_key'] ?? '');
        if ($key !== '' && array_key_exists($key, $out)) {
            $out[$key] = (string) ($row['value'] ?? '');
        }
    }

    return $out;
}

/**
 * Settings seguros para a API pública (sem SMTP / segredos).
 *
 * @return array<string, string>
 */
function settings_public(PDO $pdo): array
{
    $all = settings_all($pdo);
    foreach (array_keys($all) as $key) {
        if (str_starts_with($key, 'smtp_')) {
            unset($all[$key]);
        }
    }
    return $all;
}

function settings_get(PDO $pdo, string $key): string
{
    $all = settings_all($pdo);
    return $all[$key] ?? '';
}

/**
 * Se a aba de textos da página ficou toda vazia, desliga a página no menu
 * (feature_page_*). Se voltou a ter texto, religa (quando o plano permitir).
 */
function settings_sync_page_from_text_tab(PDO $pdo, string $tab): void
{
    $map = [
        'galeria' => [
            'feature' => 'feature_page_galeria',
            'keys' => ['gallery_eyebrow', 'gallery_title', 'gallery_subtitle'],
        ],
        'sobre' => [
            'feature' => 'feature_page_sobre',
            'keys' => [
                'about_eyebrow', 'about_title_1', 'about_title_2',
                'about_p1', 'about_p2', 'about_p3',
            ],
        ],
        'contato_page' => [
            'feature' => 'feature_page_contato',
            'keys' => ['contact_eyebrow', 'contact_title', 'contact_subtitle'],
        ],
    ];
    if (!isset($map[$tab])) {
        return;
    }

    $all = settings_all($pdo);
    $any = false;
    foreach ($map[$tab]['keys'] as $key) {
        if (trim((string) ($all[$key] ?? '')) !== '') {
            $any = true;
            break;
        }
    }

    $feature = $map[$tab]['feature'];
    if (!$any) {
        settings_save_many($pdo, [$feature => '0']);
        return;
    }

    // Não religar se o plano Essencial (ou flag) proíbe a página
    require_once __DIR__ . '/plans.php';
    $plan = plan_normalize((string) ($all['site_plan'] ?? 'medium'));
    $bundle = plan_bundle($plan);
    if (isset($bundle[$feature]) && (string) $bundle[$feature] === '0' && $plan === 'basic') {
        return;
    }
    settings_save_many($pdo, [$feature => '1']);
}

function settings_save_many(PDO $pdo, array $input): void
{
    $defs = settings_definitions();
    $now = gmdate('c');
    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, value, updated_at) VALUES (:setting_key, :value, :updated_at)
         ON CONFLICT(setting_key) DO UPDATE SET value = excluded.value, updated_at = excluded.updated_at'
    );

    foreach ($defs as $key => $def) {
        if (!array_key_exists($key, $input)) {
            continue;
        }
        // Não apagar senha SMTP ao salvar formulário com campo vazio
        if ($key === 'smtp_pass' && trim((string) $input[$key]) === '') {
            continue;
        }
        $value = settings_sanitize($key, (string) $input[$key]);
        // Campos opcionais (WhatsApp, e-mail, redes) podem ficar em branco — o front esconde o bloco
        $stmt->execute([
            ':setting_key' => $key,
            ':value' => $value,
            ':updated_at' => $now,
        ]);
    }
}

function settings_seed(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $now = gmdate('c');
    $stmt = $pdo->prepare(
        'INSERT INTO settings (setting_key, value, updated_at) VALUES (:setting_key, :value, :updated_at)'
    );
    foreach (settings_definitions() as $key => $def) {
        $stmt->execute([
            ':setting_key' => $key,
            ':value' => (string) $def['default'],
            ':updated_at' => $now,
        ]);
    }
}
