<?php

declare(strict_types=1);

/**
 * Xhybridbot (Roboto) — catálogo de ajuda pré-escrito (sem IA).
 */

/**
 * @return array{page:string,role:string,plan:string,wa_digits:string,lead_qs:string,is_staff:bool,is_client:bool}
 */
function roboto_context(?array $user, ?int $leadId = null): array
{
    require_once __DIR__ . '/auth.php';
    require_once __DIR__ . '/plans.php';

    $script = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    $page = pathinfo($script, PATHINFO_FILENAME);
    $role = (string) ($user['role'] ?? '');
    $isStaff = $user ? user_is_staff($user) : false;
    $isClient = $user ? user_is_client($user) : false;

    $plan = 'agency';
    $resolvedLead = $leadId;
    if ($resolvedLead === null && $user) {
        $own = user_crm_lead_id($user);
        if ($own) {
            $resolvedLead = $own;
        }
    }
    if ($resolvedLead !== null && $resolvedLead > 0) {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/published_sites.php';
        try {
            $row = published_site_get_by_lead(db(), $resolvedLead);
            if (is_array($row)) {
                $plan = plan_normalize((string) ($row['plan_tier'] ?? 'basic'));
            }
        } catch (Throwable $e) {
            $plan = 'basic';
        }
    }

    $wa = '';
    try {
        require_once __DIR__ . '/db.php';
        require_once __DIR__ . '/settings.php';
        $all = settings_all(db());
        $wa = preg_replace('/\D+/', '', (string) ($all['whatsapp_number'] ?? '')) ?? '';
    } catch (Throwable $e) {
        $wa = '';
    }

    $leadQs = ($resolvedLead !== null && $resolvedLead > 0) ? ('?lead_id=' . (int) $resolvedLead) : '';

    return [
        'page' => $page,
        'role' => $role,
        'plan' => $plan,
        'wa_digits' => $wa,
        'lead_qs' => $leadQs,
        'is_staff' => $isStaff,
        'is_client' => $isClient,
    ];
}

function roboto_wa_href(string $digits, string $prefill = ''): string
{
    $digits = preg_replace('/\D+/', '', $digits) ?? '';
    if ($digits === '') {
        return '';
    }
    $url = 'https://wa.me/' . $digits;
    if ($prefill !== '') {
        $url .= '?text=' . rawurlencode($prefill);
    }
    return $url;
}

/**
 * @return array<string, string>
 */
function roboto_page_intros(): array
{
    return [
        'index' => "Este é o Painel da agência: atalhos para editar a vitrine Xhybrid (contato, textos, imagens, etc.).\nCada card abre uma área. Use o menu superior para navegar rápido.\nAlguma dúvida? Escolha um tópico abaixo ou fale com a agência.",
        'lead_hub' => "Este é o Hub do seu site: atalhos para editar só o site deste negócio (lead).\nNão é a vitrine da agência — é o site do cliente publicado pelo CRM.\nAbra Contato, Textos ou Imagens conforme o seu plano permitir.",
        'contact' => "Contato define WhatsApp, telefone, e-mail, endereço e horários que aparecem no site.\nO visitante usa esses dados nos botões e no rodapé.\nSalve e confira o site público para ver a mudança.",
        'texts' => "Textos controlam frases do site (hero, seções, mensagens).\nIsso não troca o nome oficial da empresa — isso é Marca.\nEdite, salve e abra o site para revisar.",
        'images' => "Imagens cobrem logo, favicon, hero, about e galeria.\nCada slot tem um papel visual no layout.\nO plano pode limitar quantas imagens você sobe ou edita.",
        'brand' => "Marca é identidade: nome, marca curta no header, slogan e SEO de marca.\nNo Medium o cliente em geral não edita Marca (fica com a agência).\nNo Pro a edição de Marca costuma estar liberada.",
        'appearance' => "Aparência muda look, tema, fonte e layout — o “visual” do site.\nNão altera textos de contato nem a lista de serviços.\nLooks premium e animações extras dependem do plano.",
        'preset' => "Preset aplica um pacote de nicho (textos/visual base).\nPode sobrescrever conteúdo — use com cuidado e revise depois.\nDisponibilidade depende do plano e das permissões.",
        'sections' => "Visibilidade liga/desliga páginas do menu e seções da home.\nSe algo “sumiu” do site, confira os interruptores desta tela.\nAlguns planos limitam o que o cliente pode mudar.",
        'services' => "Serviços é o catálogo exibido no site (itens, ordem, status).\nHá limite máximo conforme o plano.\nSalve e veja a página de serviços no site público.",
        'plan' => "Plano define o pacote contratado (Basic, Medium ou Pro) e flags do site.\nA matriz de permissões (só Admin da agência) controla o que cada papel vê no painel.\nUpgrade libera mais edição para o cliente.",
        'password' => "Aqui você troca só a senha da sua conta de login.\nNão redefine senha de outras pessoas — isso é Usuários (Admin).\nUse senha forte e não compartilhe o acesso.",
        'leads' => "Leads lista sites publicados pelo CRM para a equipe editar.\nStaff acessa qualquer lead; cliente só o próprio.\nEntre no hub do lead para editar contato, textos e visual.",
        'users' => "Usuários gerencia contas staff e clientes (Medium/Pro) ligados a um lead.\nSó Admin cria, redefine senha de terceiros e exclui contas.\nBasic não gera login de cliente.",
        'backup' => "Backup exporta/restaura dados da vitrine — uso interno da agência.\nNão é ferramenta do cliente final.\nFaça backup antes de mudanças grandes.",
        'lead_site' => "Resumo rápido concentra contato, looks e textos principais do lead.\nCampos de Marca podem ficar bloqueados no Medium.\nSalvar sincroniza com o CRM quando a ponte estiver ativa.",
        'image_edit' => "Edição de uma imagem do catálogo (URL ou upload conforme permissão).\nSlots especiais: logo, favicon, hero e about.",
    ];
}

/**
 * @return list<array<string, mixed>>
 */
function roboto_catalog(): array
{
    $chips = [];

    $add = static function (
        array &$chips,
        string $id,
        string $group,
        string $title,
        string $body,
        string $audience = 'all',
        array $pages = ['*'],
        array $plans = ['*'],
        string $requiresPage = '',
        array $ctas = []
    ): void {
        $chips[] = [
            'id' => $id,
            'group' => $group,
            'title' => $title,
            'body' => $body,
            'audience' => $audience,
            'pages' => $pages,
            'plans' => $plans,
            'requires_page' => $requiresPage,
            'image' => '',
            'ctas' => $ctas,
        ];
    };

    // —— Core (handled specially in UI order, but also in catalog) ——
    $add($chips, 'core_screen', 'Principal', 'Como funciona esta tela?',
        '__PAGE_INTRO__', 'all', ['*'], ['*'], '', []);
    $add($chips, 'core_plans', 'Principal', 'Nossos planos',
        "Basic — site no subcaminho Xhybrid, personalização mínima; sem login completo de cliente.\n"
        . "Medium — domínio próprio e edição limitada (contato/textos/imagens conforme liberado); Marca em geral fica com a agência.\n"
        . "Pro — personalização total: Marca, aparência avançada, preset e visibilidade com mais liberdade.\n"
        . "Quer subir de plano? Fale com a agência para complementar o pagamento.",
        'all', ['*'], ['*'], '', [['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'core_missing', 'Principal', 'Por que não vejo algumas opções?',
        "O menu mostra só o que seu papel e seu plano permitem.\n"
        . "Basic: menos edição pelo cliente. Medium: edição parcial (sem Marca típica). Pro: mais telas liberadas.\n"
        . "Se falta algo essencial, peça upgrade ou ajuste à agência.",
        'all', ['*'], ['*'], '', [['label' => 'Ver planos', 'href' => '#core_plans'], ['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'core_not_found', 'Principal', 'Minha pergunta não está aqui',
        "Não encontrei essa dúvida nos tópicos. Fale com o suporte humano.",
        'all', ['*'], ['*'], '', [['label' => 'Fale conosco', 'href' => 'wa:support']]);
    $add($chips, 'core_agency', 'Principal', 'Falar com a agência',
        "A agência Xhybrid pode ajudar com plano, conteúdo e publicação.\nToque em Fale conosco para abrir o WhatsApp.",
        'all', ['*'], ['*'], '', [['label' => 'Fale conosco', 'href' => 'wa:support']]);

    // —— Geral ——
    $add($chips, 'geral_painel', 'Geral', 'O que é este painel?',
        "É a área administrativa do site: você edita conteúdo e visual sem mexer em código.\nStaff cuida da vitrine e dos leads; cliente edita só o próprio site, conforme o plano.",
        'all');
    $add($chips, 'geral_vitrine_vs_lead', 'Geral', 'Vitrine da agência vs meu site',
        "Vitrine = site da Xhybrid (Contato/Textos da agência).\nSeu negócio = site do lead (Hub do lead), publicado pelo CRM.\nCliente nunca edita a vitrine da agência — só o próprio lead.",
        'all');
    $add($chips, 'geral_salvar', 'Geral', 'Como salvar e ver no site?',
        "Em cada tela use o botão Salvar.\nDepois abra o link público do site (no hub do lead ou no CRM) e atualize a página (Ctrl+F5 se precisar).",
        'all');
    $add($chips, 'geral_hub', 'Geral', 'Como voltar ao hub?',
        "Use “←” no topo ou o card/link Hub do lead.\nStaff também volta pela lista Leads.\nNo Painel da agência, o hub é o próprio index.",
        'all');
    $add($chips, 'geral_menu', 'Geral', 'O que cada ícone do menu faz?',
        "O menu abre as mesmas áreas dos cards: Contato, Textos, Imagens, Marca, Aparência, etc.\nItens que você não vê foram bloqueados pelo plano ou pelo papel (Editor/Cliente).",
        'all');
    $add($chips, 'geral_roboto', 'Geral', 'O que o Roboto responde?',
        "Só dúvidas de funcionamento: o que é cada tela, limites do plano e por que algo não aparece.\nNão altera dados do site e não responde perguntas fora dos botões.\nSe não achar o tópico, use “Minha pergunta não está aqui”.",
        'all');

    // —— Contato ——
    $add($chips, 'contato_como', 'Contato', 'Como funciona Contato?',
        "Você informa WhatsApp, telefone, e-mail, endereço e horários.\nIsso alimenta botões, rodapé e página de contato do site.",
        'all');
    $add($chips, 'contato_wa_tel', 'Contato', 'WhatsApp vs telefone',
        "WhatsApp abre conversa no app (wa.me).\nTelefone é o número de ligação / exibição.\nPode ser o mesmo número nos dois campos, se fizer sentido.",
        'all');
    $add($chips, 'contato_endereco', 'Contato', 'Onde o endereço aparece?',
        "Rua, bairro, cidade e mapa costumam aparecer na página Contato e às vezes no rodapé.\nO link do Google Maps ajuda o visitante a traçar rota.",
        'all');
    $add($chips, 'contato_horario', 'Contato', 'Horário de funcionamento',
        "Coloque uma linha por dia, no estilo do Google Maps.\nO site exibe esse texto para o visitante saber quando ligar ou ir.",
        'all');

    // —— Textos ——
    $add($chips, 'textos_como', 'Textos', 'Como funcionam os Textos?',
        "Textos são as frases do site: títulos do hero, parágrafos e mensagens.\nCada campo tem um lugar na página. Salve e revise no site.",
        'all');
    $add($chips, 'textos_hero', 'Textos', 'Hero vs slogan',
        "Hero = destaque da home (títulos + texto principal).\nSlogan/tagline = frase curta de identidade (pode viver em Marca).\nNão misture: hero vende a oferta; slogan reforça a marca.",
        'all');
    $add($chips, 'textos_empresa', 'Textos', 'Textos mudam o nome da empresa?',
        "Não. O nome oficial da empresa é Marca / cadastro do lead.\nTextos mudam só as frases das seções.",
        'all');
    $add($chips, 'textos_seo', 'Textos', 'SEO de texto vs Marca',
        "SEO em Marca (título/descrição) afeta a identidade nas buscas.\nTextos longos nas páginas explicam o conteúdo.\nOs dois se complementam.",
        'all');

    // —— Imagens ——
    $add($chips, 'imagens_como', 'Imagens', 'Como funcionam as Imagens?',
        "Slots fixos: logo, favicon, hero, about, mais galeria/vídeos conforme o plano.\nTrocar a imagem muda o visual sem alterar os textos.",
        'all');
    $add($chips, 'imagens_slots', 'Imagens', 'Logo vs favicon vs hero vs about',
        "Logo = marca no header. Favicon = ícone da aba do navegador.\nHero = imagem grande da home. About = foto da seção sobre.",
        'all');
    $add($chips, 'imagens_bloqueio', 'Imagens', 'Por que não posso alterar imagens?',
        "Se a opção não aparece, seu plano ou papel não libera Imagens.\nBasic costuma deixar isso com a agência. Medium/Pro liberam mais.\nPeça upgrade ou peça à agência para trocar.",
        'client', ['*'], ['basic', 'medium', 'pro'], '',
        [['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'imagens_limite', 'Imagens', 'Limite de imagens da galeria',
        "Cada plano tem um teto de imagens ativas na galeria (além de logo/favicon/hero/about).\nSe atingir o limite, remova uma imagem antiga ou faça upgrade.",
        'all');

    // —— Marca ——
    $add($chips, 'marca_como', 'Marca', 'Como funciona Marca?',
        "Marca cuida do nome, logo curta no header, complemento (tag), slogan e SEO de identidade.\nÉ o “cartão de visitas” textual do site.",
        'all');
    $add($chips, 'marca_curta', 'Marca', 'O que é marca curta / tag?',
        "Marca curta = nome curto no header (quando o nome completo é longo).\nTag = complemento em destaque (cor accent).\nJuntos formam o wordmark do site.",
        'all');
    $add($chips, 'marca_medium', 'Marca', 'Por que não vejo Marca? (Medium)',
        "No Medium a edição de Marca pelo cliente costuma estar desligada — a agência define o nome.\nNo Pro você normalmente edita Marca sozinho.\nQuer liberar? Peça upgrade.",
        'client', ['*'], ['basic', 'medium'], '',
        [['label' => 'Nossos planos', 'href' => '#core_plans'], ['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'marca_vs_textos', 'Marca', 'Marca vs Textos',
        "Marca = identidade (nome, slogan, SEO de marca).\nTextos = conteúdo das páginas (hero, seções).\nMude o nome em Marca; mude a oferta em Textos.",
        'all');

    // —— Aparência ——
    $add($chips, 'aparencia_como', 'Aparência', 'Como funciona Aparência?',
        "Você escolhe look (pacote visual), tema de cores, fonte e layout.\nO site muda de “cara” sem reescrever os textos.",
        'all');
    $add($chips, 'aparencia_look', 'Aparência', 'Look vs tema vs fonte',
        "Look = combinação pronta. Tema = paleta. Fonte = tipografia. Layout = estrutura das seções.\nNo painel, looks costumam puxar tema/fonte/layout juntos.",
        'all');
    $add($chips, 'aparencia_simples', 'Aparência', 'Site pouco animado / simples',
        "Animações e looks premium costumam ser do Pro.\nMedium tem personalização limitada de propósito.\nUpgrade Pro libera mais visual e movimento.",
        'client', ['*'], ['basic', 'medium'], '',
        [['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'aparencia_premium', 'Aparência', 'O que são looks premium?',
        "São combinações visuais avançadas liberadas no plano Pro (flag de looks premium).\nSe não aparecem na lista, o plano atual não inclui.",
        'all');

    // —— Preset ——
    $add($chips, 'preset_como', 'Preset', 'O que o Preset faz?',
        "Aplica um pacote de nicho (ex.: eletricista, clínica) com textos e visual base.\nAcelera o começo do site.",
        'all');
    $add($chips, 'preset_apaga', 'Preset', 'Preset apaga meus textos?',
        "Pode sobrescrever textos e settings do pacote.\nFaça backup (agência) ou anote o que customizou antes de aplicar.\nDepois revise Contato, Textos e Imagens.",
        'all');
    $add($chips, 'preset_plano', 'Preset', 'Posso usar no meu plano?',
        "Depende da flag de preset no plano e da permissão da sua conta.\nSe o card Preset não aparece, não está liberado — fale com a agência.",
        'all');

    // —— Visibilidade ——
    $add($chips, 'vis_como', 'Visibilidade', 'O que Visibilidade controla?',
        "Liga/desliga páginas do menu (Sobre, Projetos, Contato) e seções da home.\nÉ o interruptor do que o visitante vê.",
        'all');
    $add($chips, 'vis_sumiu', 'Visibilidade', 'Por que uma página sumiu do menu?',
        "Provavelmente a flag da página está desligada em Visibilidade/Plano.\nReligue e salve. Se não puder editar, peça à agência.",
        'all');
    $add($chips, 'vis_home', 'Visibilidade', 'Seções da home',
        "Hero, features, área de atendimento, FAQ, CTA etc. podem ser ligados/desligados.\nMenos seções = página mais direta.",
        'all');

    // —— Serviços ——
    $add($chips, 'serv_como', 'Serviços', 'Como editar Serviços?',
        "Cadastre itens do catálogo (nome, descrição, imagem, ordem).\nAtivos aparecem no site. Respeite o limite do plano.",
        'all');
    $add($chips, 'serv_limite', 'Serviços', 'Limite de serviços do plano',
        "Basic/Medium/Pro têm tetos diferentes de serviços ativos.\nSe não conseguir criar mais, remova um item ou faça upgrade.",
        'all');

    // —— Senha ——
    $add($chips, 'senha_como', 'Senha', 'Como trocar minha senha?',
        "Abra Senha, informe a nova senha e confirme.\nSó altera a conta com a qual você está logado.",
        'all');
    $add($chips, 'senha_outro', 'Senha', 'Consigo trocar senha de outro usuário?',
        "Não nesta tela. Só Admin em Usuários pode redefinir senha de terceiros.",
        'all');

    // —— Plano / upgrade ——
    $add($chips, 'plano_inclui', 'Plano', 'O que inclui Basic / Medium / Pro?',
        "Basic: site completo no subcaminho, pouca personalização pelo cliente.\nMedium: domínio e edição limitada (sem Marca típica pelo cliente).\nPro: personalização total (Marca, looks, seções).",
        'all');
    $add($chips, 'plano_upgrade', 'Plano', 'Como fazer upgrade / complementar?',
        "Fale com a agência pelo WhatsApp para complementar o pagamento e mudar o plano.\nDepois do upgrade, novas telas podem aparecer no seu menu.",
        'client', ['*'], ['basic', 'medium', 'pro'], '',
        [['label' => 'Fale conosco', 'href' => 'wa:upgrade']]);
    $add($chips, 'plano_basic_login', 'Plano', 'Por que Basic não tem login de cliente?',
        "Basic é operado pela agência. Login de cliente existe a partir do Medium/Pro.\nAssim o dono do negócio edita o site com segurança conforme o pacote.",
        'all');

    // —— Staff ——
    $add($chips, 'staff_leads', 'Equipe', 'Como funcionam Leads?',
        "Lista sites publicados do CRM. Abra o hub do lead para editar.\nSoft delete/lixeira e pagamentos ficam no CRM; aqui é a vitrine publicada.",
        'staff', ['*'], ['*'], 'leads');
    $add($chips, 'staff_users', 'Equipe', 'Como funcionam Usuários?',
        "Crie Editor/Admin ou Cliente Medium/Pro vinculados a um lead.\nCliente Medium exige plano Medium; Cliente Pro exige Pro.\nBasic não cria login de cliente.",
        'staff', ['*'], ['*'], 'users');
    $add($chips, 'staff_backup', 'Equipe', 'Como funciona Backup?',
        "Exporta o SQLite da vitrine e permite restaurar.\nUse antes de presets agressivos ou migrações. Só Admin.",
        'staff', ['*'], ['*'], 'backup');
    $add($chips, 'staff_perms', 'Equipe', 'Onde edito permissões?',
        "Em Plano (visão agência, sem lead_id): matriz Papéis × páginas e Planos × recursos.\nSó Admin. Isso controla o menu do Editor e dos clientes.",
        'staff', ['*'], ['*'], 'plan');
    $add($chips, 'staff_crm', 'Equipe', 'CRM vs painel Xhybrid',
        "CRM captura leads e publica. Xhybrid admin edita o site já publicado.\nMudanças de nome/contato no lead podem sincronizar nos dois sentidos conforme a ponte.",
        'staff', ['*'], ['*'], 'leads');

    return $chips;
}

/**
 * @param array{page:string,role:string,plan:string,wa_digits:string,lead_qs:string,is_staff:bool,is_client:bool} $ctx
 * @return list<array<string, mixed>>
 */
function roboto_chips_for(array $ctx, ?array $user): array
{
    require_once __DIR__ . '/auth.php';

    $page = $ctx['page'];
    $plan = $ctx['plan'];
    $isStaff = !empty($ctx['is_staff']);
    $isClient = !empty($ctx['is_client']);
    $intros = roboto_page_intros();
    $out = [];

    foreach (roboto_catalog() as $chip) {
        $audience = (string) ($chip['audience'] ?? 'all');
        if ($audience === 'staff' && !$isStaff) {
            continue;
        }
        if ($audience === 'client' && !$isClient && !$isStaff) {
            continue;
        }
        // Cliente não vê chips staff; staff vê client chips (ajuda a explicar ao cliente)
        if ($audience === 'client' && $isStaff) {
            // ok
        }

        $req = (string) ($chip['requires_page'] ?? '');
        if ($req !== '' && $user && !user_can_page($user, $req)) {
            continue;
        }

        $pages = $chip['pages'] ?? ['*'];
        if (!is_array($pages)) {
            $pages = ['*'];
        }
        if (!in_array('*', $pages, true) && !in_array($page, $pages, true)) {
            // Ainda listamos no repertório geral se o grupo não for "só esta página"
            // Regra do plano: pages lista onde o chip é relevante; se não for *, exige match
            continue;
        }

        $plans = $chip['plans'] ?? ['*'];
        if (!is_array($plans)) {
            $plans = ['*'];
        }
        if (!in_array('*', $plans, true)) {
            if ($plan === 'agency') {
                // agência: mostrar chips de plano genéricos
            } elseif (!in_array($plan, $plans, true)) {
                continue;
            }
        }

        $body = (string) ($chip['body'] ?? '');
        if ($body === '__PAGE_INTRO__') {
            $body = $intros[$page] ?? $intros['index'];
        }

        $ctas = [];
        foreach ($chip['ctas'] ?? [] as $cta) {
            if (!is_array($cta)) {
                continue;
            }
            $href = (string) ($cta['href'] ?? '');
            $label = (string) ($cta['label'] ?? '');
            if ($label === '') {
                continue;
            }
            if (str_starts_with($href, 'wa:')) {
                $kind = substr($href, 3);
                $msg = $kind === 'upgrade'
                    ? 'Olá! Vim pelo Roboto no painel. Quero falar sobre upgrade/complementar pagamento do plano.'
                    : 'Olá! Vim pelo Roboto no painel e preciso de suporte humano.';
                $wa = roboto_wa_href($ctx['wa_digits'], $msg);
                if ($wa === '') {
                    continue;
                }
                $href = $wa;
            } elseif ($href === '#core_plans') {
                // front trata como selecionar chip
            } elseif ($href !== '' && !str_starts_with($href, 'http') && !str_starts_with($href, '#')) {
                $href .= $ctx['lead_qs'];
            }
            $ctas[] = ['label' => $label, 'href' => $href];
        }

        // Se WA vazio e era CTA essencial de suporte, acrescenta nota no body
        if ($chip['id'] === 'core_not_found' || $chip['id'] === 'core_agency') {
            if ($ctx['wa_digits'] === '') {
                $body .= $isStaff
                    ? "\n\n(WhatsApp da agência não configurado em Contato → whatsapp_number.)"
                    : "\n\nPeça o WhatsApp à agência — o canal ainda não está configurado neste painel.";
            }
        }

        $out[] = [
            'id' => $chip['id'],
            'group' => $chip['group'],
            'title' => $chip['title'],
            'body' => $body,
            'image' => (string) ($chip['image'] ?? ''),
            'ctas' => $ctas,
        ];
    }

    return $out;
}

/**
 * Payload JSON para o front.
 *
 * @return array{context: array<string,mixed>, chips: list<array<string,mixed>>}
 */
function roboto_payload(?array $user, ?int $leadId = null): array
{
    $ctx = roboto_context($user, $leadId);
    return [
        'context' => [
            'page' => $ctx['page'],
            'role' => $ctx['role'],
            'plan' => $ctx['plan'],
            'has_wa' => $ctx['wa_digits'] !== '',
        ],
        'chips' => roboto_chips_for($ctx, $user),
    ];
}

/**
 * Roboto enxuto na página pública /planos.html (sem login).
 *
 * @return array{context: array<string,mixed>, chips: list<array<string,mixed>>}
 */
function roboto_planos_public_payload(?PDO $pdo = null): array
{
    require_once __DIR__ . '/public_offer.php';
    require_once __DIR__ . '/settings.php';
    require_once __DIR__ . '/db.php';

    $pdo = $pdo ?? db();
    $offer = public_offer_get($pdo);
    $wa = preg_replace('/\D+/', '', settings_get($pdo, 'whatsapp_number')) ?? '';

    $planLines = [];
    foreach ($offer['plans'] as $p) {
        $price = number_format(((int) $p['planCents']) / 100, 2, ',', '.');
        $maint = number_format(((int) $p['maintenanceCents']) / 100, 2, ',', '.');
        $bits = implode('; ', array_slice($p['bullets'], 0, 3));
        $planLines[] = "{$p['label']} — R$ {$price}/mês + manutenção R$ {$maint}. {$bits}.";
    }
    $summaryBody = "Resumo da oferta atual:\n" . implode("\n", $planLines)
        . "\n\nO plano Pleno costuma ser o mais escolhido. No checkout você escolhe Asaas ou Stripe.";

    $howBody =
        "1) Escolha Basic, Pleno ou Plus na página.\n"
        . "2) Selecione Asaas (Brasil) ou Stripe.\n"
        . "3) Aceite os Termos (abra o resumo na caixinha) e continue para o checkout.\n"
        . "4) Após o pagamento, o site é liberado conforme a oferta.\n\n"
        . "Basic: subdomínio e personalização mínima.\n"
        . "Pleno: domínio próprio e personalização limitada.\n"
        . "Plus: personalização ampliada, marca e looks premium.";

    $maintBody =
        "A manutenção mensal cobre hospedagem/SSL, monitoramento básico e suporte do pacote.\n"
        . "O valor aparece em cada card sob o preço do plano.\n"
        . "Atrasos podem suspender o site até a regularização (veja os Termos).";

    $payBody =
        "Asaas: comum para cobrança no Brasil (PIX/boleto/cartão conforme configuração).\n"
        . "Stripe: cartão internacional e fluxo do provedor Stripe.\n"
        . "O link de cobrança usa o provedor que você marcar nesta página.";

    $lateBody =
        "Inadimplência pode suspender o site e o acesso ao painel até o pagamento.\n"
        . "Detalhes estão nos Termos de Serviço (botão Termos nesta página).\n"
        . "Se precisar negociar, fale com a agência pelo WhatsApp.";

    $refBody =
        "Indicação elegível: R$ 50 por indicação paga, até R$ 150 no acumulado da regra vigente.\n"
        . "Condições completas nos Termos (seção de indicação).";

    $waCta = [];
    $waHref = roboto_wa_href($wa, 'Olá! Vim pela página de Planos e quero tirar uma dúvida.');
    if ($waHref !== '') {
        $waCta[] = ['label' => 'Falar no WhatsApp', 'href' => $waHref];
    }

    $chips = [
        [
            'id' => 'planos_how',
            'group' => 'Planos',
            'title' => 'Como funciona cada plano?',
            'body' => $howBody,
            'image' => '',
            'ctas' => $waCta,
        ],
        [
            'id' => 'planos_summary',
            'group' => 'Planos',
            'title' => 'Resuma os planos',
            'body' => $summaryBody,
            'image' => '',
            'ctas' => [],
        ],
        [
            'id' => 'planos_maint',
            'group' => 'Planos',
            'title' => 'O que é a manutenção mensal?',
            'body' => $maintBody,
            'image' => '',
            'ctas' => [],
        ],
        [
            'id' => 'planos_pay',
            'group' => 'Pagamento',
            'title' => 'Asaas ou Stripe?',
            'body' => $payBody,
            'image' => '',
            'ctas' => [],
        ],
        [
            'id' => 'planos_late',
            'group' => 'Pagamento',
            'title' => 'E se atrasar o pagamento?',
            'body' => $lateBody,
            'image' => '',
            'ctas' => $waCta,
        ],
        [
            'id' => 'planos_ref',
            'group' => 'Indicacao',
            'title' => 'Como funciona a indicação?',
            'body' => $refBody,
            'image' => '',
            'ctas' => [],
        ],
    ];

    return [
        'context' => [
            'page' => 'planos',
            'role' => 'guest',
            'plan' => 'public',
            'has_wa' => $wa !== '',
        ],
        'chips' => $chips,
    ];
}
