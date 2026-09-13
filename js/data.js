/**
 * Configuração central da Xhybrid.
 * Valores padrão — sobrescritos por /api/settings.php quando disponível.
 */
const SITE_DEFAULTS = {
  whatsapp_number: "5511999999999",
  whatsapp_message:
    "Olá! Vim pelo site da Xhybrid e quero um orçamento de site.",
  email: "contato@xhybrid.com.br",
  instagram_url: "https://instagram.com/xhybrid",
  instagram_label: "@xhybrid — projetos e bastidores",
  facebook_url: "",
  facebook_label: "",
  tiktok_url: "",
  tiktok_label: "",
  address: "",
  maps_url: "",
  contact_whatsapp_desc: "O jeito mais rápido de pedir um orçamento",
  contact_response_title: "Tempo de resposta",
  contact_response_text: "Respondemos em até 1 dia útil",
  nav_index: "Início",
  nav_sobre: "Sobre",
  nav_galeria: "Projetos",
  nav_contato: "Contato",
  footer_tagline:
    "Agência de criação de sites, manutenção e tecnologia — presença digital profissional para o seu negócio.",
  footer_link_sobre: "Sobre nós",
  fab_label: "Fale conosco",
  footer_hours_title: "Horário de funcionamento",
  footer_hours_line1: "Segunda a sexta: 9h – 18h",
  footer_hours_line2: "Sábado: 9h – 13h",
  footer_hours_line3: "Domingo: fechado",
  footer_hours_line4: "",
  footer_hours_line5: "",
  footer_hours_line6: "",
  footer_hours_line7: "",
  appearance_theme: "preto",
  appearance_font: "saas",
  appearance_layout: "soft",
  appearance_media: "classic",
  appearance_look: "xhybrid-signature",
  appearance_combination_id: "",
  framework_skin: "none",
  framework_bootswatch: "",
  appearance_locked_by_admin: "0",
  home_badge: "Sites & tecnologia",
  home_hero_title_1: "Seu negócio,",
  home_hero_title_2: "online de verdade.",
  home_hero_text:
    "Somos a Xhybrid — criação de sites, manutenção e tecnologia para empresas que querem presença digital profissional.",
  home_btn_gallery: "Ver projetos",
  home_btn_quote: "Orçamento",
  home_weave_title: "O que fazemos",
  home_weave_subtitle:
    "Do site institucional à manutenção contínua — tecnologia sob medida para o seu negócio.",
  home_feat_1_title: "Criação de sites",
  home_feat_1_icon: "layout",
  home_feat_1_text:
    "Landing pages e sites corporativos modernos, rápidos e alinhados à sua marca.",
  home_feat_2_title: "Manutenção",
  home_feat_2_icon: "wrench",
  home_feat_2_text:
    "Atualizações, backups, performance e correções para o site ficar sempre no ar.",
  home_feat_3_title: "Tecnologia",
  home_feat_3_icon: "cpu",
  home_feat_3_text:
    "Integrações, automações e melhorias digitais para otimizar o dia a dia.",
  home_destaques_title: "Projetos em destaque",
  home_destaques_subtitle: "Alguns trabalhos de criação e desenvolvimento.",
  home_destaques_link: "Ver portfólio completo",
  home_cta_title_1: "Tem um projeto?",
  home_cta_title_2: "A gente desenvolve.",
  home_cta_text:
    "Conte o que precisa — site novo, manutenção ou melhoria tecnológica — e montamos a melhor proposta.",
  home_cta_btn: "Orçamento",
  about_eyebrow: "Sobre nós",
  about_title_1: "Tecnologia,",
  about_title_2: "com clareza.",
  about_p1:
    "A Xhybrid nasceu para ajudar empresas a terem presença digital profissional: sites bem feitos, manutenção confiável e tecnologia aplicada ao negócio.",
  about_p2:
    "Cuidamos do visual, da performance e da operação — do primeiro briefing à publicação, com comunicação direta e prazos claros.",
  about_p3:
    "Mais do que páginas no ar, entregamos uma base digital sólida para você atender clientes, divulgar serviços e crescer online.",
  about_btn: "Ver projetos",
  about_stat_1_value: "100%",
  about_stat_1_text: "Foco em entrega e resultado",
  about_stat_2_value: "Ágil",
  about_stat_2_text: "Processo claro do briefing à publicação",
  about_stat_3_value: "Sob medida",
  about_stat_3_text: "Soluções alinhadas ao seu negócio",
  gallery_eyebrow: "Portfolio",
  gallery_title: "Projetos",
  gallery_subtitle:
    "Sites e soluções que já entregamos. Toque em uma foto para ampliar.",
  contact_eyebrow: "Get in touch",
  contact_title: "Contato",
  contact_subtitle:
    "Orçamentos e dúvidas — escolha o canal que preferir.",
  contact_form_title: "Escreva para nós",
  contact_form_intro: "Preencha abaixo e sua mensagem abre direto no seu e-mail.",
  contact_form_btn: "Enviar mensagem",
  brand_name: "Xhybrid",
  logo_url: "",
  brand_tagline: "Sites, manutenção e tecnologia",
  brand_city: "",
  brand_seo_title: "Xhybrid — Criação de sites, manutenção e tecnologia",
  brand_seo_description:
    "Agência Xhybrid: criação de sites, manutenção e tecnologia para empresas. Orçamento rápido pelo WhatsApp.",
  area_text: "",
  urgency_enabled: "0",
  urgency_label: "24h",
  seo_sobre_title: "",
  seo_sobre_description: "",
  seo_galeria_title: "",
  seo_galeria_description: "",
  seo_contato_title: "",
  seo_contato_description: "",
  analytics_ga4_id: "",
  analytics_meta_pixel_id: "",
  section_hero: "1",
  section_features: "1",
  section_works: "1",
  section_area: "0",
  section_testimonials: "0",
  section_faq: "0",
  section_cta: "1",
  testimonials_title: "O que dizem os clientes",
  testimonial_1_name: "",
  testimonial_1_city: "",
  testimonial_1_text: "",
  testimonial_2_name: "",
  testimonial_2_city: "",
  testimonial_2_text: "",
  testimonial_3_name: "",
  testimonial_3_city: "",
  testimonial_3_text: "",
  faq_title: "Perguntas frequentes",
  faq_1_q: "",
  faq_1_a: "",
  faq_2_q: "",
  faq_2_a: "",
  faq_3_q: "",
  faq_3_a: "",
  faq_4_q: "",
  faq_4_a: "",
  site_plan: "medium",
  feature_page_sobre: "1",
  feature_page_galeria: "1",
  feature_page_contato: "1",
  feature_animations: "1",
  feature_looks_premium: "0",
  feature_preset_nicho: "1",
  limit_services: "6",
  limit_gallery: "12",
};

/** Estado vivo do site (defaults + API) */
const SITE = { ...SITE_DEFAULTS };

function site(key) {
  // Respeita string vazia salva no admin (não repor default — ex.: Instagram apagado)
  if (Object.prototype.hasOwnProperty.call(SITE, key)) {
    const v = SITE[key];
    return v == null ? "" : String(v);
  }
  return SITE_DEFAULTS[key] || "";
}

function applySiteSettings(data) {
  if (!data || typeof data !== "object" || Array.isArray(data) || data.error) {
    return false;
  }
  let applied = 0;
  Object.keys(SITE_DEFAULTS).forEach((key) => {
    if (Object.prototype.hasOwnProperty.call(data, key) && typeof data[key] === "string") {
      SITE[key] = data[key];
      applied += 1;
    }
  });
  return applied > 0;
}

function whatsappLink(message) {
  const digits = String(site("whatsapp_number") || "").replace(/\D+/g, "");
  if (!digits) return "#";
  const msg = message == null ? site("whatsapp_message") : message;
  return `https://wa.me/${digits}?text=${encodeURIComponent(msg)}`;
}

function whatsappUrl() {
  return whatsappLink();
}

function whatsappProduto(nome) {
  return whatsappLink(
    `Olá! Me interessei por "${nome}" (${site("brand_name")}). Pode me passar mais detalhes?`,
  );
}

/** Compat: código antigo */
let WHATSAPP_NUMBER = SITE.whatsapp_number;
let WHATSAPP_DEFAULT_MESSAGE = SITE.whatsapp_message;
let INSTAGRAM_URL = SITE.instagram_url;
let EMAIL = SITE.email;
let WHATSAPP_URL = whatsappUrl();

function syncContactGlobals() {
  WHATSAPP_NUMBER = site("whatsapp_number");
  WHATSAPP_DEFAULT_MESSAGE = site("whatsapp_message");
  INSTAGRAM_URL = site("instagram_url");
  EMAIL = site("email");
  WHATSAPP_URL = whatsappUrl();
}

function pageCopyIsEmpty(page) {
  const keys = {
    sobre: [
      "about_eyebrow",
      "about_title_1",
      "about_title_2",
      "about_p1",
      "about_p2",
      "about_p3",
    ],
    galeria: ["gallery_eyebrow", "gallery_title", "gallery_subtitle"],
    contato: ["contact_eyebrow", "contact_title", "contact_subtitle"],
  }[page];
  if (!keys) return false;
  return keys.every((k) => !site(k).trim());
}

function pageIsEnabled(page) {
  const flag = {
    sobre: "feature_page_sobre",
    galeria: "feature_page_galeria",
    contato: "feature_page_contato",
  }[page];
  if (!flag) return true;
  if (site(flag) === "0") return false;
  // Textos todos vazios → esconde aba mesmo se a flag ainda estiver ligada
  if (pageCopyIsEmpty(page)) return false;
  return true;
}

function getNavLinks() {
  const links = [
    { href: leadPageHref("index.html"), label: site("nav_index"), page: "index" },
  ];
  if (pageIsEnabled("sobre")) {
    links.push({ href: leadPageHref("sobre.html"), label: site("nav_sobre"), page: "sobre" });
  }
  if (pageIsEnabled("galeria")) {
    links.push({ href: leadPageHref("galeria.html"), label: site("nav_galeria"), page: "galeria" });
  }
  if (pageIsEnabled("contato")) {
    links.push({ href: leadPageHref("contato.html"), label: site("nav_contato"), page: "contato" });
  }
  return links;
}

/** Base pública do lead: /{slug}/{leadId}{letra} — ou "" na vitrine. */
function leadPublicBase() {
  try {
    const p = window.__xhybridLeadPath;
    if (p && p.slug && p.code && p.leadId) {
      return "/" + String(p.slug).toLowerCase() + "/" + String(p.leadId) + String(p.code).toLowerCase();
    }
  } catch (_) { /* ignore */ }
  return "";
}

/** Link de página preservando o path do lead quando ativo. */
function leadPageHref(file) {
  const name = String(file || "index.html").replace(/^\//, "");
  const base = leadPublicBase();
  if (!base) return name;
  if (name === "index.html" || name === "") return base;
  return base + "/" + name;
}

/** Looks liberados conforme plano / flag premium */
function allowedLookPresets() {
  const premium = site("feature_looks_premium") === "1";
  const plan = String(site("site_plan") || "basic").toLowerCase();
  const normalized =
    plan === "essencial" || plan === "basic"
      ? "basic"
      : plan === "profissional" || plan === "medium"
        ? "medium"
        : plan === "personalizado" || plan === "pro"
          ? "pro"
          : plan;
  if (premium || normalized === "pro") {
    return LOOK_PRESETS;
  }
  const basicPalettes =
    normalized === "basic"
      ? ["Neutro"]
      : ["Neutro", "Azul", "Ciano", "Verde", "Quente"];
  return LOOK_PRESETS.filter((l) => basicPalettes.includes(l.palette || "Neutro"));
}

function isLookAllowed(lookId) {
  return allowedLookPresets().some((l) => l.id === lookId);
}

const products = [
  {
    id: "landing-page",
    nome: "Landing Page",
    descricao:
      "Página de captura rápida, objetiva e otimizada para conversão de leads.",
    imageKey: "landing",
    categoria: "Criação",
  },
  {
    id: "site-corporativo",
    nome: "Site Corporativo",
    descricao:
      "Site institucional moderno para apresentar a empresa, serviços e contato.",
    imageKey: "corporate",
    categoria: "Criação",
  },
  {
    id: "loja-online",
    nome: "Loja Online",
    descricao:
      "Estrutura digital para catálogo, pedidos e presença comercial na web.",
    imageKey: "shop",
    categoria: "E-commerce",
  },
  {
    id: "manutencao",
    nome: "Manutenção Contínua",
    descricao:
      "Atualizações, backups, correções e monitoramento para o site permanecer estável.",
    imageKey: "maintenance",
    categoria: "Manutenção",
  },
  {
    id: "integracoes",
    nome: "Integrações",
    descricao:
      "WhatsApp, formulários, CRM e automações conectadas ao fluxo do negócio.",
    imageKey: "integrations",
    categoria: "Tecnologia",
  },
  {
    id: "identidade-web",
    nome: "Identidade Web",
    descricao:
      "Visual, tipografia e layout alinhados à marca para uma presença profissional.",
    imageKey: "branding",
    categoria: "Design",
  },
];

const THEMES = [
  {
    id: "marrom-claro",
    nome: "Marrom Claro",
    descricao: "Creme, bege e caramelo",
    escuro: false,
    swatch: ["#f5efe4", "#c49a6c", "#7a5a3c"],
  },
  {
    id: "marrom-escuro",
    nome: "Marrom Escuro",
    descricao: "Chocolate, café e creme",
    escuro: true,
    swatch: ["#2a1d15", "#c89a67", "#efe3d1"],
  },
  {
    id: "branco",
    nome: "Branco Minimalista",
    descricao: "Branco, off-white e grafite",
    escuro: false,
    swatch: ["#ffffff", "#ececea", "#1a1a1a"],
  },
  {
    id: "preto",
    nome: "Preto Elegante",
    descricao: "Preto, grafite e branco",
    escuro: true,
    swatch: ["#0f0f10", "#3a3a3d", "#f2f2f2"],
  },
  {
    id: "azul",
    nome: "Azul",
    descricao: "Marinho, azul médio e claro",
    escuro: true,
    swatch: ["#0f1d33", "#3f6fb3", "#dbe7f7"],
  },
  {
    id: "rosa",
    nome: "Rosa",
    descricao: "Rosa queimado, claro e vinho",
    escuro: false,
    swatch: ["#fbf1f2", "#c47a86", "#7a2e40"],
  },
  {
    id: "verde",
    nome: "Verde Floresta",
    descricao: "Verde profundo e menta",
    escuro: true,
    swatch: ["#0f1f18", "#3dba8a", "#d8f3e7"],
  },
  {
    id: "teal",
    nome: "Teal",
    descricao: "Petróleo e ciano",
    escuro: true,
    swatch: ["#0b1c1f", "#2ec4b6", "#d7f5f2"],
  },
  {
    id: "amber",
    nome: "Amber",
    descricao: "Areia e âmbar",
    escuro: false,
    swatch: ["#f7f1e6", "#d4a11a", "#3a2a12"],
  },
  {
    id: "cinza",
    nome: "Cinza Claro",
    descricao: "Cinza frio e grafite",
    escuro: false,
    swatch: ["#f3f4f6", "#6b7280", "#111827"],
  },
  {
    id: "indigo",
    nome: "Indigo",
    descricao: "Azul-índigo noturno",
    escuro: true,
    swatch: ["#12122a", "#818cf8", "#e0e7ff"],
  },
  {
    id: "graphite",
    nome: "Graphite",
    descricao: "Chumbo e azul aço",
    escuro: true,
    swatch: ["#171a1f", "#7aa2c8", "#e8eef5"],
  },
  {
    id: "oceano",
    nome: "Oceano",
    descricao: "Azul claro e espuma",
    escuro: false,
    swatch: ["#eef6fb", "#2f7fb5", "#0b2a3d"],
  },
  {
    id: "lime",
    nome: "Lime Dark",
    descricao: "Preto com lima",
    escuro: true,
    swatch: ["#10140f", "#a3e635", "#f7fee7"],
  },
  {
    id: "vinho",
    nome: "Vinho",
    descricao: "Bordeaux e blush",
    escuro: true,
    swatch: ["#2a1218", "#d4787a", "#f7e8ea"],
  },
  {
    id: "cobre",
    nome: "Cobre",
    descricao: "Bronze e cobre quente",
    escuro: true,
    swatch: ["#24180f", "#d0894a", "#f5e6d6"],
  },
  {
    id: "slate",
    nome: "Slate",
    descricao: "Ardósia e prata",
    escuro: true,
    swatch: ["#1a1f26", "#9aa8b5", "#e8eef3"],
  },
  {
    id: "neon",
    nome: "Neon",
    descricao: "Escuro com ciano elétrico",
    escuro: true,
    swatch: ["#0b1218", "#2ee6d6", "#dffcf8"],
  },
  {
    id: "berry",
    nome: "Berry",
    descricao: "Magenta e vinho",
    escuro: true,
    swatch: ["#24101c", "#e06aaa", "#fce8f2"],
  },
  {
    id: "midnight",
    nome: "Midnight",
    descricao: "Azul-noite profundo",
    escuro: true,
    swatch: ["#0b1020", "#6b8fd6", "#e6ecf8"],
  },
  {
    id: "menta",
    nome: "Menta",
    descricao: "Verde fresco claro",
    escuro: false,
    swatch: ["#effaf5", "#2f9e7a", "#14352b"],
  },
  {
    id: "peonia",
    nome: "Peônia",
    descricao: "Pêssego e rosa suave",
    escuro: false,
    swatch: ["#fff1ee", "#d46a6a", "#4a1f22"],
  },
  {
    id: "lavanda",
    nome: "Lavanda",
    descricao: "Lilás suave e violeta",
    escuro: false,
    swatch: ["#f4f0fb", "#7a63b8", "#2a2140"],
  },
  {
    id: "mostarda",
    nome: "Mostarda",
    descricao: "Dourado e mostarda",
    escuro: false,
    swatch: ["#f7f1df", "#c49a1a", "#3a2e10"],
  },
  {
    id: "gelo",
    nome: "Gelo",
    descricao: "Branco gelo e azul frio",
    escuro: false,
    swatch: ["#f5fafc", "#3d7ea6", "#142432"],
  },
  {
    id: "sunset",
    nome: "Sunset",
    descricao: "Coral e pôr do sol",
    escuro: false,
    swatch: ["#fff4ea", "#e07040", "#3d1f14"],
  },
  {
    id: "sangue",
    nome: "Sangue",
    descricao: "Vermelho escuro e carvão",
    escuro: true,
    swatch: ["#120608", "#b91c1c", "#fecaca"],
  },
  {
    id: "violeta",
    nome: "Violeta",
    descricao: "Roxo neon em fundo quase preto",
    escuro: true,
    swatch: ["#0c0618", "#a855f7", "#f3e8ff"],
  },
  {
    id: "neon-roxo",
    nome: "Neon Roxo",
    descricao: "Magenta elétrico e violeta",
    escuro: true,
    swatch: ["#0a0514", "#d946ef", "#fae8ff"],
  },
  {
    id: "ameixa",
    nome: "Ameixa",
    descricao: "Plum profundo e rubi",
    escuro: true,
    swatch: ["#14081a", "#9f1239", "#fce7f3"],
  },
  {
    id: "fuchsia-night",
    nome: "Fuchsia Night",
    descricao: "Fúcsia neon no escuro",
    escuro: true,
    swatch: ["#0d0612", "#e879f9", "#fdf4ff"],
  },
];

/** Pacotes de tipografia (data-font) */
const FONT_PACKS = [
  { id: "tech", nome: "Tech", descricao: "Space Grotesk + IBM Plex" },
  { id: "soft", nome: "Soft", descricao: "Outfit + Sora" },
  { id: "editorial", nome: "Editorial", descricao: "Fraunces + Source Sans" },
  { id: "saas", nome: "SaaS", descricao: "Plus Jakarta + Manrope" },
  { id: "mono", nome: "Mono", descricao: "Space Grotesk + JetBrains" },
  { id: "display", nome: "Display", descricao: "Syne + DM Sans" },
  { id: "geometric", nome: "Geometric", descricao: "Archivo + Public Sans" },
  { id: "classic", nome: "Classic", descricao: "Playfair + Lato" },
  { id: "rounded", nome: "Rounded", descricao: "Nunito + Nunito Sans" },
  { id: "condensed", nome: "Condensed", descricao: "Barlow Condensed + Barlow" },
  { id: "inter", nome: "Inter", descricao: "Inter" },
  { id: "montserrat", nome: "Montserrat", descricao: "Montserrat + Work Sans" },
  { id: "raleway", nome: "Raleway", descricao: "Raleway" },
  { id: "poppins", nome: "Poppins", descricao: "Poppins" },
  { id: "slab", nome: "Slab", descricao: "Roboto Slab + Fira Sans" },
  { id: "baskerville", nome: "Baskerville", descricao: "Libre Baskerville" },
  { id: "garamond", nome: "Garamond", descricao: "Cormorant Garamond" },
  { id: "figtree", nome: "Figtree", descricao: "Figtree" },
  { id: "lexend", nome: "Lexend", descricao: "Lexend" },
  { id: "work", nome: "Work Sans", descricao: "Work Sans" },
];

/** Moldes de layout (data-layout) — visual: raios, glass, densidade */
const LAYOUT_PRESETS = [
  { id: "soft", nome: "Soft Glass", descricao: "Arredondado e glass" },
  { id: "sharp", nome: "Sharp Tech", descricao: "Reto e técnico" },
  { id: "bento", nome: "Bento", descricao: "Cards assimétricos" },
  { id: "editorial", nome: "Editorial", descricao: "Limpo e central" },
  { id: "pill", nome: "Pill SaaS", descricao: "Máximo arredondado" },
  { id: "compact", nome: "Compact", descricao: "Denso e objetivo" },
  { id: "frame", nome: "Frame", descricao: "Bordas marcadas" },
  { id: "magazine", nome: "Magazine", descricao: "Editorial denso" },
  { id: "loft", nome: "Loft", descricao: "Amplo sem glass" },
  { id: "strip", nome: "Strip", descricao: "Faixas horizontais" },
];

/**
 * Posição das imagens / composição (data-media).
 * Independente do molde — no mobile sempre empilha.
 */
const MEDIA_PRESETS = [
  { id: "classic", nome: "Clássico", descricao: "Texto esq · imagem dir" },
  { id: "flip", nome: "Invertido", descricao: "Imagem esq · texto dir" },
  { id: "hero-flip", nome: "Hero invertido", descricao: "Só o hero troca de lado" },
  { id: "about-flip", nome: "Sobre invertido", descricao: "Só o sobre troca de lado" },
  { id: "stack-media", nome: "Imagem no topo", descricao: "Mídia acima do texto" },
  { id: "stack-copy", nome: "Texto no topo", descricao: "Texto acima da mídia" },
  { id: "center", nome: "Central", descricao: "Hero centralizado" },
  { id: "media-wide", nome: "Mídia larga", descricao: "Imagem domina o hero" },
  { id: "copy-wide", nome: "Texto largo", descricao: "Texto domina o hero" },
  { id: "gallery-dense", nome: "Galeria densa", descricao: "Mais colunas nos projetos" },
];

/**
 * Looks prontos — cada um muda o site por completo
 * (cor + fonte + molde + posição de imagens + botões/figuras).
 * Ordenados por paleta e, dentro dela, do mais escuro ao mais claro.
 */
const LOOK_PRESETS = [
  /* Neutro — vitrine Xhybrid primeiro */
  {
    id: "xhybrid-signature",
    nome: "Xhybrid Signature",
    descricao: "Vitrine premium — preto, glass transparente e motion",
    palette: "Neutro",
    theme: "preto",
    font: "saas",
    layout: "soft",
    media: "classic",
    agencyExclusive: true,
    swatch: ["#050505", "rgba(255,255,255,0.2)", "#f4f4f5"],
  },
  {
    id: "obsidian",
    nome: "Obsidian",
    descricao: "Preto profundo, painéis transparentes",
    palette: "Neutro",
    theme: "preto",
    font: "display",
    layout: "frame",
    media: "copy-wide",
    swatch: ["#0a0a0b", "#2a2a2e", "#f5f5f5"],
  },
  {
    id: "tech-glass",
    nome: "Tech Glass",
    descricao: "SaaS premium — preto, glass e bento",
    palette: "Neutro",
    theme: "preto",
    font: "tech",
    layout: "soft",
    media: "classic",
    swatch: ["#000000", "#2a2a2a", "#ffffff"],
  },
  {
    id: "ash-glass",
    nome: "Ash Glass",
    descricao: "Cinza escuro translúcido, layout compacto",
    palette: "Neutro",
    theme: "slate",
    font: "saas",
    layout: "compact",
    media: "stack-media",
    swatch: ["#1a1f26", "#9aa8b5", "#e8eef3"],
  },
  {
    id: "sharp-saas",
    nome: "Sharp SaaS",
    descricao: "Bento assimétrico + hero centralizado",
    palette: "Neutro",
    theme: "graphite",
    font: "saas",
    layout: "sharp",
    media: "center",
    swatch: ["#171a1f", "#7aa2c8", "#e8eef5"],
  },
  {
    id: "ivory-soft",
    nome: "Ivory Soft",
    descricao: "Branco e cinza claro, muito ar",
    palette: "Neutro",
    theme: "cinza",
    font: "soft",
    layout: "pill",
    media: "stack-copy",
    swatch: ["#f3f4f6", "#9ca3af", "#111827"],
  },
  {
    id: "editorial",
    nome: "Editorial",
    descricao: "Lista tipográfica numerada, sem cards",
    palette: "Neutro",
    theme: "branco",
    font: "editorial",
    layout: "editorial",
    media: "flip",
    swatch: ["#ffffff", "#1a1a1a", "#ececea"],
  },

  /* Azul */
  {
    id: "navy-depth",
    nome: "Navy Depth",
    descricao: "Azul-noite denso, colunas de revista",
    palette: "Azul",
    theme: "midnight",
    font: "geometric",
    layout: "magazine",
    media: "about-flip",
    swatch: ["#0b1020", "#6b8fd6", "#e6ecf8"],
  },
  {
    id: "azure-blast",
    nome: "Azure Blast",
    descricao: "Azul elétrico forte, mídia larga",
    palette: "Azul",
    theme: "azul",
    font: "geometric",
    layout: "frame",
    media: "media-wide",
    swatch: ["#0a1628", "#2563eb", "#dbeafe"],
  },
  {
    id: "indigo-flare",
    nome: "Indigo Flare",
    descricao: "Índigo profundo, revista densa",
    palette: "Azul",
    theme: "indigo",
    font: "display",
    layout: "magazine",
    media: "about-flip",
    swatch: ["#12122a", "#6366f1", "#e0e7ff"],
  },
  {
    id: "ocean-vivid",
    nome: "Ocean Vivid",
    descricao: "Azul oceano vivo, tipografia limpa",
    palette: "Azul",
    theme: "oceano",
    font: "soft",
    layout: "editorial",
    media: "flip",
    swatch: ["#e0f2fe", "#0284c7", "#0c4a6e"],
  },

  /* Ciano / teal */
  {
    id: "neon-night",
    nome: "Neon Night",
    descricao: "Faixas horizontais + formulário no topo",
    palette: "Ciano",
    theme: "neon",
    font: "mono",
    layout: "strip",
    media: "hero-flip",
    swatch: ["#0b1218", "#2ee6d6", "#dffcf8"],
  },
  {
    id: "teal-rush",
    nome: "Teal Rush",
    descricao: "Teal saturado, glass e texto largo",
    palette: "Ciano",
    theme: "teal",
    font: "tech",
    layout: "soft",
    media: "copy-wide",
    swatch: ["#042f2e", "#14b8a6", "#ccfbf1"],
  },

  /* Verde */
  {
    id: "volt-lime",
    nome: "Volt Lime",
    descricao: "Verde lima neon, faixas técnicas",
    palette: "Verde",
    theme: "lime",
    font: "mono",
    layout: "strip",
    media: "hero-flip",
    swatch: ["#0c1408", "#a3e635", "#f7fee7"],
  },

  /* Quente */
  {
    id: "amber-flare",
    nome: "Amber Flare",
    descricao: "Âmbar quente intenso, bento irregular",
    palette: "Quente",
    theme: "amber",
    font: "saas",
    layout: "bento",
    media: "flip",
    swatch: ["#1c1205", "#f59e0b", "#fff7ed"],
  },
  {
    id: "copper-heat",
    nome: "Copper Heat",
    descricao: "Cobre metálico, molduras marcadas",
    palette: "Quente",
    theme: "cobre",
    font: "classic",
    layout: "frame",
    media: "classic",
    swatch: ["#1a1008", "#d97706", "#fef3c7"],
  },
  {
    id: "fire-sunset",
    nome: "Fire Sunset",
    descricao: "Laranja-coral forte, empilhado",
    palette: "Quente",
    theme: "sunset",
    font: "condensed",
    layout: "loft",
    media: "stack-media",
    swatch: ["#2a1008", "#ea580c", "#ffedd5"],
  },
  {
    id: "warm-studio",
    nome: "Warm Studio",
    descricao: "Tudo central, pílulas e 1 destaque grande",
    palette: "Quente",
    theme: "marrom-claro",
    font: "classic",
    layout: "loft",
    media: "center",
    swatch: ["#f5efe4", "#c49a6c", "#7a5a3c"],
  },

  /* Vermelho */
  {
    id: "blood-noir",
    nome: "Blood Noir",
    descricao: "Vermelho sangue no preto, blocos duros",
    palette: "Vermelho",
    theme: "sangue",
    font: "display",
    layout: "sharp",
    media: "classic",
    swatch: ["#120608", "#dc2626", "#fecaca"],
  },
  {
    id: "crimson-volt",
    nome: "Crimson Volt",
    descricao: "Vermelho rubi intenso, cards em bloco",
    palette: "Vermelho",
    theme: "vinho",
    font: "display",
    layout: "sharp",
    media: "classic",
    swatch: ["#2a0a12", "#e11d48", "#ffe4e8"],
  },
  {
    id: "plum-ember",
    nome: "Plum Ember",
    descricao: "Ameixa e rubi, molduras quentes",
    palette: "Vermelho",
    theme: "ameixa",
    font: "classic",
    layout: "frame",
    media: "media-wide",
    swatch: ["#14081a", "#9f1239", "#fce7f3"],
  },

  /* Roxo / magenta */
  {
    id: "neon-orchid",
    nome: "Neon Orchid",
    descricao: "Magenta elétrico, faixas neon",
    palette: "Roxo",
    theme: "neon-roxo",
    font: "mono",
    layout: "strip",
    media: "hero-flip",
    swatch: ["#0a0514", "#d946ef", "#fae8ff"],
  },
  {
    id: "violet-pulse",
    nome: "Violet Pulse",
    descricao: "Roxo neon, colunas elétricas",
    palette: "Roxo",
    theme: "violeta",
    font: "geometric",
    layout: "magazine",
    media: "about-flip",
    swatch: ["#0c0618", "#a855f7", "#f3e8ff"],
  },
  {
    id: "cyber-magenta",
    nome: "Cyber Magenta",
    descricao: "Fúcsia neon, glass cyber",
    palette: "Roxo",
    theme: "fuchsia-night",
    font: "saas",
    layout: "soft",
    media: "copy-wide",
    swatch: ["#0d0612", "#e879f9", "#fdf4ff"],
  },
  {
    id: "berry-pop",
    nome: "Berry Pop",
    descricao: "Magenta vibrante, pílulas e centro",
    palette: "Roxo",
    theme: "berry",
    font: "rounded",
    layout: "pill",
    media: "center",
    swatch: ["#1f0a18", "#ec4899", "#fce7f3"],
  },
  {
    id: "petal-sky",
    nome: "Petal Sky",
    descricao: "Rosa suave e azul claro, cards escalonados",
    palette: "Roxo",
    theme: "peonia",
    font: "rounded",
    layout: "bento",
    media: "flip",
    swatch: ["#fff1ee", "#7eb6d9", "#d46a6a"],
  },
];

const DEFAULT_THEME = "preto";
const DEFAULT_FONT = "tech";
const DEFAULT_LAYOUT = "soft";
const DEFAULT_MEDIA = "classic";
const DEFAULT_LOOK = "xhybrid-signature";
const THEME_STORAGE_KEY = "xhybrid-theme";
const FONT_STORAGE_KEY = "xhybrid-font";
const LAYOUT_STORAGE_KEY = "xhybrid-layout";
const MEDIA_STORAGE_KEY = "xhybrid-media";
const LOOK_STORAGE_KEY = "xhybrid-look";

/** Preenchido após carregar /api/images.php */
let galleryPhotos = [];

function bindProductImages() {
  products.forEach((p) => {
    // Sem slug/URL na API: sem imagem (card usa frame vazio, não robô)
    p.imagem = (p.imageKey && IMAGES[p.imageKey]) || "";
  });
}

/** Substitui o catálogo hardcoded pelos serviços do admin/API. */
function applyServicesCatalog(rows) {
  if (!Array.isArray(rows) || !rows.length) return false;
  products.splice(0, products.length);
  const max = Math.max(1, parseInt(site("limit_services") || "6", 10) || 6);
  rows.slice(0, max).forEach((row) => {
    products.push({
      id: String(row.id ?? row.title ?? ""),
      nome: String(row.title || "Serviço"),
      descricao: String(row.description || ""),
      imageKey: String(row.image_slug || ""),
      categoria: String(row.category || "Serviço"),
      imagem: "",
    });
  });
  return products.length > 0;
}

/**
 * @param {Array} apiRows
 * @param {boolean} apiOk - true se /api/images.php respondeu com sucesso
 */
function rebuildGalleryPhotos(apiRows, apiOk) {
  if (apiOk) {
    galleryPhotos = (apiRows || [])
      .filter((row) => {
        if (!row.slug || row.slug === "favicon" || row.slug === "logo") return false;
        if (String(row.slug).startsWith("video-")) return false;
        return true;
      })
      .map((row) => ({
        src: driveToSrc(row.url) || row.url,
        alt: row.title || row.slug,
        legenda: row.title || row.slug,
        descricao: (row.description || "").trim(),
        preco: (row.price || "").trim(),
        precoPromo: (row.promo_price || "").trim(),
        _slug: row.slug,
      }))
      .filter((foto) => Boolean(foto.src));

    const maxExtra = Math.max(0, parseInt(site("limit_gallery") || "12", 10) || 12);
    const core = galleryPhotos.filter((f) => f._slug === "hero" || f._slug === "about");
    const extras = galleryPhotos.filter((f) => f._slug !== "hero" && f._slug !== "about").slice(0, maxExtra);
    galleryPhotos = [...core, ...extras].map(({ _slug, ...rest }) => rest);
    return;
  }

  galleryPhotos = [
    ...products
      .filter((p) => p.imagem)
      .map((p) => ({
        src: p.imagem,
        alt: p.nome,
        legenda: p.categoria,
        descricao: "",
        preco: "",
        precoPromo: "",
      })),
    ...(IMAGES.hero
      ? [{
          src: IMAGES.hero,
          alt: "Ambiente de desenvolvimento com notebook e código",
          legenda: "Nosso trabalho",
          descricao: "",
          preco: "",
          precoPromo: "",
        }]
      : []),
    ...(IMAGES.about
      ? [{
          src: IMAGES.about,
          alt: "Equipe colaborando em projeto digital",
          legenda: "Xhybrid em ação",
          descricao: "",
          preco: "",
          precoPromo: "",
        }]
      : []),
  ];
}
