/* SVG icons (Lucide-style) */
const ICONS = {
  sparkles: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>',
  arrowRight: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
  messageCircle: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
  menu: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
  x: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  palette: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
  check: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
  instagram: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>',
  facebook: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>',
  tiktok: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>',
  mapPin: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>',
  mail: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
  clock: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  send: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>',
  whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.89 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.94L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.33 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41"/></svg>',
  /** Bolsa/cifrão para CTAs de orçamento */
  quote: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" width="16" height="16"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/><path d="M12 10v6"/><path d="M10 14h4"/></svg>',
  shuffle: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 14 4 4-4 4"/><path d="m18 2 4 4-4 4"/><path d="M2 18h1.973a4 4 0 0 0 3.3-1.7l5.454-8.6a4 4 0 0 1 3.3-1.7H22"/><path d="M2 6h1.972a4 4 0 0 1 3.6 2.2"/><path d="M22 18h-6.041a4 4 0 0 1-3.3-1.8l-.245-.4"/></svg>',
};

function isThemeId(v) {
  return typeof v === "string" && THEMES.some((t) => t.id === v);
}

function isFontId(v) {
  return typeof v === "string" && FONT_PACKS.some((f) => f.id === v);
}

function isLayoutId(v) {
  return typeof v === "string" && LAYOUT_PRESETS.some((l) => l.id === v);
}

function isMediaId(v) {
  return typeof v === "string" && MEDIA_PRESETS.some((m) => m.id === v);
}

function isLookId(v) {
  return typeof v === "string" && allowedLookPresets().some((l) => l.id === v);
}

function getLookPreset(lookId) {
  const allowed = allowedLookPresets();
  return allowed.find((l) => l.id === lookId) || allowed[0] || LOOK_PRESETS[0];
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

/** HTML do preço (promo risca o preço normal) */
function renderPriceHtml(foto) {
  const preco = (foto.preco || "").trim();
  const promo = (foto.precoPromo || "").trim();
  if (!preco && !promo) return "";
  if (promo && preco) {
    return `<span class="gallery-price"><s class="gallery-price__old">${escapeHtml(preco)}</s> <span class="gallery-price__promo">${escapeHtml(promo)}</span></span>`;
  }
  const valor = promo || preco;
  return `<span class="gallery-price"><span class="gallery-price__current">${escapeHtml(valor)}</span></span>`;
}

function renderGalleryCaption(foto) {
  const price = renderPriceHtml(foto);
  return `<span class="gallery__caption-title">${escapeHtml(foto.legenda)}</span>${price}`;
}

function renderLightboxCaption(foto) {
  const parts = [`<strong class="lightbox__title">${escapeHtml(foto.legenda)}</strong>`];
  if ((foto.descricao || "").trim()) {
    parts.push(`<span class="lightbox__desc">${escapeHtml(foto.descricao.trim())}</span>`);
  }
  const price = renderPriceHtml(foto);
  if (price) parts.push(price);
  return parts.join("");
}

function applyTheme(themeId) {
  const root = document.documentElement;
  root.setAttribute("data-theme", themeId);
  const theme = THEMES.find((t) => t.id === themeId);
  root.classList.toggle("dark", theme?.escuro ?? false);
}

function applyFont(fontId) {
  document.documentElement.setAttribute("data-font", fontId);
}

function applyLayout(layoutId) {
  document.documentElement.setAttribute("data-layout", layoutId);
}

function applyMedia(mediaId) {
  document.documentElement.setAttribute("data-media", mediaId);
}

function applyLook(lookId) {
  document.documentElement.setAttribute("data-look", lookId);
}

function cacheAppearance(theme, font, layout, media, look) {
  try {
    localStorage.setItem(THEME_STORAGE_KEY, theme);
    localStorage.setItem(FONT_STORAGE_KEY, font);
    localStorage.setItem(LAYOUT_STORAGE_KEY, layout);
    localStorage.setItem(MEDIA_STORAGE_KEY, media);
    if (look) localStorage.setItem(LOOK_STORAGE_KEY, look);
  } catch (_) {
    /* ignore */
  }
}

function resolveAppearance() {
  let look = site("appearance_look");
  if (!isLookId(look)) {
    try {
      const savedLook = localStorage.getItem(LOOK_STORAGE_KEY);
      if (isLookId(savedLook)) look = savedLook;
    } catch (_) { /* ignore */ }
  }
  if (!isLookId(look)) look = DEFAULT_LOOK;

  const preset = getLookPreset(look);
  const themeFromSite = site("appearance_theme");
  const fontFromSite = site("appearance_font");
  const layoutFromSite = site("appearance_layout");
  const mediaFromSite = site("appearance_media");
  return {
    look: preset.id,
    theme: isThemeId(themeFromSite) ? themeFromSite : preset.theme,
    font: isFontId(fontFromSite) ? fontFromSite : preset.font,
    layout: isLayoutId(layoutFromSite) ? layoutFromSite : preset.layout,
    media: isMediaId(mediaFromSite) ? mediaFromSite : preset.media,
  };
}

function applyAppearance(appearance, { persist = true } = {}) {
  const look = isLookId(appearance.look) ? appearance.look : DEFAULT_LOOK;
  const preset = getLookPreset(look);
  const theme = isThemeId(appearance.theme) ? appearance.theme : preset.theme;
  const font = isFontId(appearance.font) ? appearance.font : preset.font;
  const layout = isLayoutId(appearance.layout) ? appearance.layout : preset.layout;
  const media = isMediaId(appearance.media) ? appearance.media : preset.media;

  applyLook(look);
  applyTheme(theme);
  applyFont(font);
  applyLayout(layout);
  applyMedia(media);
  applyFrameworkSkin();

  if (persist) cacheAppearance(theme, font, layout, media, look);

  return { look, theme, font, layout, media };
}

/** Skin CDN opcional (bootswatch|bulma|tailwind), antes de styles.css; polish depois. */
function applyFrameworkSkin() {
  const allowed = new Set(["none", "bootswatch", "bulma", "tailwind"]);
  const skin = String(site("framework_skin") || "none").toLowerCase();
  const active = allowed.has(skin) ? skin : "none";

  document.querySelectorAll("link[data-framework-skin], script[data-framework-skin]").forEach((el) => el.remove());
  document.documentElement.setAttribute("data-framework-skin", active);

  if (active === "none") return;

  const head = document.head;
  const mainCss = document.querySelector('link[href*="css/styles.css"]');
  const insertLink = (href) => {
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = href;
    link.setAttribute("data-framework-skin", "1");
    if (mainCss) head.insertBefore(link, mainCss);
    else head.appendChild(link);
  };

  if (active === "bootswatch") {
    const theme = String(site("framework_bootswatch") || "darkly").replace(/[^a-z0-9-]/gi, "") || "darkly";
    insertLink("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css");
    insertLink(`https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/${theme}/bootstrap.min.css`);
  } else if (active === "bulma") {
    insertLink("https://cdn.jsdelivr.net/npm/bulma@1.0.2/css/bulma.min.css");
  } else if (active === "tailwind") {
    // Depois de styles + polish, para reforçar ritmo sem preflight.
    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = "css/skin-tailwind.css";
    link.setAttribute("data-framework-skin", "1");
    const polish = document.querySelector('link[href*="framework-skins.css"]');
    const anchor = polish || mainCss;
    if (anchor && anchor.parentNode) {
      anchor.parentNode.insertBefore(link, anchor.nextSibling);
    } else {
      head.appendChild(link);
    }
  }
}

function setupAppearancePreviewListener() {
  if (window.__xhybridAppearanceListenerBound) return;
  window.__xhybridAppearanceListenerBound = true;
  window.addEventListener("message", (event) => {
    if (event.origin !== window.location.origin) return;
    const data = event.data;
    if (!data || data.type !== "xhybrid-appearance-preview") return;
    applyAppearance(
      {
        look: data.look,
        theme: data.theme,
        font: data.font,
        layout: data.layout,
        media: data.media,
      },
      { persist: false }
    );
  });

  try {
    const isPreview = new URLSearchParams(window.location.search).has("preview");
    if (isPreview && window.parent && window.parent !== window) {
      window.parent.postMessage({ type: "xhybrid-appearance-ready" }, window.location.origin);
    }
  } catch (_) {
    /* ignore */
  }
}

function isLeadSite() {
  try {
    return !!(window.__xhybridLeadPath && window.__xhybridLeadPath.leadId);
  } catch (_) {
    return false;
  }
}

/** Logo do header: na vitrine usa imagem da agência; no lead usa marca curta + tag (ou logo própria). */
function splitBrandMark(name) {
  const raw = String(name || "").trim().replace(/\s+/g, " ");
  if (!raw) return { short: "", tag: "" };
  const chunks = raw.split(/\s*(?:\/\/|\||·|•|–|—)\s*|\s+-\s+|,\s+/);
  const primary = (chunks[0] || "")
    .trim()
    .replace(/^[\s.,;:|/\\]+|[\s.,;:|/\\]+$/g, "");
  const words = primary.split(/\s+/).filter(Boolean);
  if (!words.length) return { short: "", tag: "" };
  const chars = (s) => Array.from(String(s));
  const cut = (s, n) => chars(s).slice(0, n).join("");
  // CSS .site-logo__mark aplica uppercase (evita bug de acentos no JS)
  const short = cut(words[0], 18);
  let tag = "";
  if (words[1] && chars(words[1]).length <= 18) {
    const w = words[1].toLocaleLowerCase("pt-BR");
    tag = cut(w.charAt(0).toLocaleUpperCase("pt-BR") + w.slice(1), 16);
  } else if (words.length === 1 && chunks[1]) {
    const rest = String(chunks[1]).trim().split(/\s+/).filter(Boolean);
    if (rest[0] && chars(rest[0]).length <= 18) {
      const w0 = rest[0];
      tag =
        chars(w0).length <= 4
          ? cut(w0.toLocaleUpperCase("pt-BR"), 16)
          : cut(
              w0.toLocaleLowerCase("pt-BR").replace(/^./u, (c) => c.toLocaleUpperCase("pt-BR")),
              16,
            );
    }
  }
  return { short, tag };
}

function brandMarkParts(fullName) {
  const short = String(site("brand_short") || "").trim();
  const tag = String(site("brand_tag") || "").trim();
  if (short) return { short, tag };
  return splitBrandMark(fullName);
}

function resolveLogoInner(brand) {
  const name = brand || "Xhybrid";
  const leadLogo = isLeadSite() ? String(site("logo_url") || "").trim() : "";
  if (leadLogo) {
    return `<img class="site-logo__img" src="${escapeHtml(leadLogo)}" alt="${escapeHtml(name)}" width="140" height="40" referrerpolicy="no-referrer">`;
  }
  // Lead publicado: nunca herdar logo da agência Xhybrid — wordmark curto
  if (isLeadSite()) {
    const parts = brandMarkParts(name);
    if (!parts.short) return escapeHtml(name);
    const tagHtml = parts.tag
      ? `<span class="site-logo__tag">${escapeHtml(parts.tag)}</span>`
      : "";
    return `<span class="site-logo__mark">${escapeHtml(parts.short)}</span>${tagHtml}`;
  }
  if (IMAGES.logo) {
    return `<img class="site-logo__img" src="${escapeHtml(IMAGES.logo)}" alt="${escapeHtml(name)}" width="140" height="40" referrerpolicy="no-referrer">`;
  }
  if (name.toLowerCase() === "xhybrid") {
    return `X<span class="text-primary italic">hybrid</span>`;
  }
  const parts = brandMarkParts(name);
  if (parts.short && parts.short.toLowerCase() !== name.toLowerCase()) {
    const tagHtml = parts.tag
      ? `<span class="site-logo__tag">${escapeHtml(parts.tag)}</span>`
      : "";
    return `<span class="site-logo__mark">${escapeHtml(parts.short)}</span>${tagHtml}`;
  }
  return escapeHtml(name);
}

function renderHeader(currentPage) {
  const navLinks = getNavLinks().map((l) => {
    const active = l.page === currentPage ? " is-active" : "";
    return `<a href="${l.href}" class="${active.trim()}">${escapeHtml(l.label)}</a>`;
  }).join("");

  const mobileLinks = getNavLinks().map((l) => {
    const active = l.page === currentPage ? " is-active" : "";
    return `<li><a href="${l.href}" class="${active.trim()}">${escapeHtml(l.label)}</a></li>`;
  }).join("");

  const brand = site("brand_name") || "Xhybrid";
  const logoInner = resolveLogoInner(brand);

  return `
    <header class="site-header">
      <div class="container site-header__inner">
        <a href="${leadPageHref("index.html")}" class="site-logo" aria-label="${escapeHtml(brand)} — página inicial">
          ${logoInner}
        </a>
        <nav class="site-nav" aria-label="Navegação principal">${navLinks}</nav>
        <div class="header-actions">
          <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Abrir menu" aria-expanded="false">
            ${ICONS.menu}
          </button>
        </div>
      </div>
      <nav class="mobile-nav" id="mobile-nav" aria-label="Menu móvel">
        <ul class="container">${mobileLinks}</ul>
      </nav>
    </header>`;
}

function renderFooter() {
  const year = new Date().getFullYear();
  const waNum = site("whatsapp_number").trim();
  const wa = waNum ? whatsappUrl() : "";
  const ig = site("instagram_url").trim();
  const fb = site("facebook_url").trim();
  const tt = site("tiktok_url").trim();
  const mail = site("email").trim();
  const address = site("address").trim();
  const maps = site("maps_url").trim();
  const brand = site("brand_name") || "Xhybrid";
  const hoursTitle = site("footer_hours_title");
  const hoursLines = [1, 2, 3, 4, 5, 6, 7]
    .map((n) => site("footer_hours_line" + n).trim())
    .filter(Boolean)
    .map((line) => `<li>${escapeHtml(line)}</li>`)
    .join("");

  const hoursBlock = hoursLines
    ? `
        <div class="site-footer__hours">
          <p class="site-footer__heading">${escapeHtml(hoursTitle || "Horário")}</p>
          <ul class="site-footer__hours-list">${hoursLines}</ul>
        </div>`
    : "";

  const brandHtml =
    !isLeadSite() && brand.toLowerCase() === "xhybrid"
      ? `X<span class="text-primary italic">hybrid</span>`
      : escapeHtml(brand);

  const socialBits = [
    wa ? `<a href="${escapeHtml(wa)}" target="_blank" rel="noreferrer" aria-label="WhatsApp">${ICONS.messageCircle}</a>` : "",
    ig ? `<a href="${escapeHtml(ig)}" target="_blank" rel="noreferrer" aria-label="Instagram">${ICONS.instagram}</a>` : "",
    fb ? `<a href="${escapeHtml(fb)}" target="_blank" rel="noreferrer" aria-label="Facebook">${ICONS.facebook}</a>` : "",
    tt ? `<a href="${escapeHtml(tt)}" target="_blank" rel="noreferrer" aria-label="TikTok">${ICONS.tiktok}</a>` : "",
    mail ? `<a href="mailto:${escapeHtml(mail)}" aria-label="E-mail">${ICONS.mail}</a>` : "",
    maps ? `<a href="${escapeHtml(maps)}" target="_blank" rel="noreferrer" aria-label="Google Maps">${ICONS.mapPin}</a>` : "",
  ].filter(Boolean).join("");

  return `
    <footer class="site-footer">
      <div class="container site-footer__grid">
        <div>
          <p class="site-footer__brand">${brandHtml}</p>
          <p class="site-footer__tagline">${escapeHtml(site("footer_tagline"))}</p>
        </div>
        <nav aria-label="Links do rodapé">
          <p class="site-footer__heading">Navegue</p>
          <ul class="site-footer__links">
            ${pageIsEnabled("sobre") ? `<li><a href="${leadPageHref("sobre.html")}">${escapeHtml(site("footer_link_sobre"))}</a></li>` : ""}
            ${pageIsEnabled("galeria") ? `<li><a href="${leadPageHref("galeria.html")}">${escapeHtml(site("nav_galeria"))}</a></li>` : ""}
            ${pageIsEnabled("contato") ? `<li><a href="${leadPageHref("contato.html")}">${escapeHtml(site("nav_contato"))}</a></li>` : ""}
          </ul>
        </nav>
        <div>
          <p class="site-footer__heading">Fale com a gente</p>
          ${socialBits ? `<div class="social-links">${socialBits}</div>` : ""}
          ${mail ? `<p class="site-footer__tagline" style="margin-top:0.75rem">${escapeHtml(mail)}</p>` : ""}
          ${address ? `<p class="site-footer__tagline" style="margin-top:0.5rem">${escapeHtml(address)}</p>` : ""}
        </div>
        ${hoursBlock}
      </div>
      <div class="site-footer__bottom">
        © ${year} ${escapeHtml(brand)}${site("brand_city") ? ` · ${escapeHtml(site("brand_city"))}` : ""}.
      </div>
    </footer>`;
}

function renderFabWhatsApp() {
  if (!site("whatsapp_number").trim()) {
    return "";
  }
  return `
    <a href="${escapeHtml(whatsappUrl())}" target="_blank" rel="noopener noreferrer" aria-label="Fale conosco pelo WhatsApp" class="fab-whatsapp">
      <span class="fab-whatsapp__label">${escapeHtml(site("fab_label"))}</span>
      <span class="fab-whatsapp__btn-wrap">
        <span class="fab-whatsapp__ping" aria-hidden="true"></span>
        <span class="fab-whatsapp__btn">${ICONS.whatsapp}</span>
      </span>
    </a>`;
}

function setupLayout(currentPage) {
  const headerMount = document.getElementById("site-header");
  const footerMount = document.getElementById("site-footer");
  const fabMount = document.getElementById("fab-whatsapp");

  if (headerMount) headerMount.innerHTML = renderHeader(currentPage);
  if (footerMount) footerMount.innerHTML = renderFooter();
  if (fabMount) fabMount.innerHTML = renderFabWhatsApp();

  applyAppearance(resolveAppearance(), { persist: !window.__xhybridLeadPath });
  setupAppearancePreviewListener();
  setupMobileMenu();
}

function setupMobileMenu() {
  const toggle = document.getElementById("menu-toggle");
  const nav = document.getElementById("mobile-nav");
  if (!toggle || !nav) return;

  function closeMenu() {
    nav.classList.remove("is-open");
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Abrir menu");
    toggle.innerHTML = ICONS.menu;
  }

  function openMenu() {
    nav.classList.add("is-open");
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Fechar menu");
    toggle.innerHTML = ICONS.x;
  }

  toggle.addEventListener("click", () => {
    if (nav.classList.contains("is-open")) closeMenu();
    else openMenu();
  });

  nav.querySelectorAll("a").forEach((a) => a.addEventListener("click", closeMenu));

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeMenu();
  });
}

function setupGallery() {
  const gallery = document.getElementById("gallery");
  const lightbox = document.getElementById("lightbox");
  if (!gallery || !lightbox) return;

  if (!galleryPhotos.length) {
    gallery.innerHTML = `<li class="empty-state" style="column-span:all;break-inside:avoid">Nenhum projeto disponível no momento.</li>`;
    return;
  }

  galleryPhotos.forEach((foto, i) => {
    const li = document.createElement("li");
    li.className = "gallery__item";
    li.innerHTML = `
      <button type="button" class="gallery__btn" data-index="${i}" aria-label="Ampliar foto: ${escapeHtml(foto.alt)}">
        <img src="${escapeHtml(foto.src)}" alt="${escapeHtml(foto.alt)}" width="1024" height="1024" loading="lazy" referrerpolicy="no-referrer">
        <span class="gallery__caption">${renderGalleryCaption(foto)}</span>
      </button>`;
    gallery.appendChild(li);
    attachDriveFallback(li.querySelector("img"));
  });

  const lightboxImg = document.getElementById("lightbox-img");
  const lightboxCaption = document.getElementById("lightbox-caption");
  const closeBtn = document.getElementById("lightbox-close");

  function openLightbox(index) {
    const foto = galleryPhotos[index];
    if (!foto) return;
    lightboxImg.src = foto.src;
    lightboxImg.alt = foto.alt;
    attachDriveFallback(lightboxImg);
    lightboxCaption.innerHTML = renderLightboxCaption(foto);
    lightbox.classList.add("is-open");
    document.body.style.overflow = "hidden";
  }

  function closeLightbox() {
    lightbox.classList.remove("is-open");
    document.body.style.overflow = "";
  }

  gallery.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-index]");
    if (!btn) return;
    openLightbox(Number(btn.getAttribute("data-index")));
  });

  closeBtn.addEventListener("click", closeLightbox);
  lightbox.addEventListener("click", (e) => {
    if (e.target === lightbox) closeLightbox();
  });

  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeLightbox();
  });
}

function setupHomeDestaques() {
  const grid = document.getElementById("destaques-grid");
  if (!grid) return;
  grid.innerHTML = "";

  products.slice(0, 3).forEach((p) => {
    const hasImg = Boolean(String(p.imagem || "").trim());
    const li = document.createElement("li");
    li.className = "product-card product-card--bordered reveal";
    li.innerHTML = `
      <div class="product-card__img-wrap${hasImg ? "" : " is-media-empty"}">
        ${
          hasImg
            ? `<img src="${escapeHtml(p.imagem)}" alt="${escapeHtml(p.nome)}" width="1024" height="768" loading="lazy" referrerpolicy="no-referrer">`
            : `<img alt="" width="1024" height="768" hidden class="is-media-empty" aria-hidden="true">`
        }
      </div>
      <div class="product-card__body">
        <p class="product-card__category">${escapeHtml(p.categoria)}</p>
        <h3 class="product-card__title">${escapeHtml(p.nome)}</h3>
      </div>`;
    grid.appendChild(li);
    const img = li.querySelector("img");
    if (hasImg && img) attachDriveFallback(img);
  });
}

function applyBrandMeta() {
  const page = document.body.dataset.page || "index";
  const pageKey =
    page === "sobre" ? "sobre" : page === "galeria" ? "galeria" : page === "contato" ? "contato" : "";
  const pageTitle = pageKey ? site(`seo_${pageKey}_title`).trim() : "";
  const pageDesc = pageKey ? site(`seo_${pageKey}_description`).trim() : "";
  const title = pageTitle || site("brand_seo_title");
  const desc = pageDesc || site("brand_seo_description");
  const brand = site("brand_name");
  if (title) document.title = title;
  const metaDesc = document.querySelector('meta[name="description"]');
  if (metaDesc && desc) metaDesc.setAttribute("content", desc);
  const author = document.querySelector('meta[name="author"]');
  if (author && brand) author.setAttribute("content", brand);

  document.querySelectorAll("[data-error-home]").forEach((el) => {
    el.textContent = brand ? `Voltar a ${brand}` : "Voltar ao início";
  });

  const ensureMeta = (attr, key, value) => {
    if (!value) return;
    let el = document.querySelector(`meta[${attr}="${key}"]`);
    if (!el) {
      el = document.createElement("meta");
      el.setAttribute(attr, key);
      document.head.appendChild(el);
    }
    el.setAttribute("content", value);
  };

  ensureMeta("property", "og:title", title);
  ensureMeta("property", "og:description", desc);
  ensureMeta("name", "twitter:title", title);
  ensureMeta("name", "twitter:description", desc);

  const ogSrc = IMAGES.hero || IMAGES.logo || IMAGES.favicon || "";
  if (ogSrc) {
    let absolute = ogSrc;
    try {
      absolute = new URL(ogSrc, window.location.href).href;
    } catch (_) {
      /* keep relative */
    }
    ensureMeta("property", "og:image", absolute);
    ensureMeta("name", "twitter:image", absolute);
    ensureMeta("name", "twitter:card", "summary_large_image");
  }
}

function applyAnalytics() {
  const ga = site("analytics_ga4_id").trim();
  const pixel = site("analytics_meta_pixel_id").trim();
  if (ga && /^G-[A-Z0-9]+$/i.test(ga) && !document.getElementById("xh-ga4")) {
    const s = document.createElement("script");
    s.id = "xh-ga4";
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga)}`;
    document.head.appendChild(s);
    const inline = document.createElement("script");
    inline.textContent =
      "window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config'," +
      JSON.stringify(ga) +
      ");";
    document.head.appendChild(inline);
  }
  if (pixel && /^\d{5,20}$/.test(pixel) && !document.getElementById("xh-meta-pixel")) {
    const inline = document.createElement("script");
    inline.id = "xh-meta-pixel";
    inline.textContent =
      "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init'," +
      JSON.stringify(pixel) +
      ");fbq('track','PageView');";
    document.head.appendChild(inline);
  }
}

function applySections() {
  document.querySelectorAll("[data-section]").forEach((el) => {
    const key = el.getAttribute("data-section");
    if (!key) return;
    const flag = site(`section_${key}`);
    el.hidden = flag === "0";
  });

  document.querySelectorAll("[data-requires-page]").forEach((el) => {
    const page = el.getAttribute("data-requires-page");
    if (!page) return;
    el.hidden = !pageIsEnabled(page);
  });

  document.querySelectorAll(".hero__actions").forEach((actions) => {
    const visible = [...actions.children].filter((child) => !child.hidden);
    actions.classList.toggle("hero__actions--single", visible.length <= 1);
  });

  const urgency = document.querySelector("[data-urgency-badge]");
  if (urgency) {
    const on = site("urgency_enabled") === "1";
    urgency.hidden = !on;
    const label = urgency.querySelector("[data-site='urgency_label']");
    if (label) label.textContent = site("urgency_label");
  }

  const areaBlock = document.querySelector("[data-section='area']");
  if (areaBlock && !site("area_text").trim()) {
    areaBlock.hidden = true;
  }
}

function enforcePlanPages() {
  const page = document.body.dataset.page || "index";
  if (page === "sobre" || page === "galeria" || page === "contato") {
    if (!pageIsEnabled(page)) {
      window.location.replace(leadPageHref("index.html"));
    }
  }
}

/** Mapa hash ↔ página do menu (página única). */
function spaHashToPage(hash) {
  const id = String(hash || "").replace(/^#/, "").toLowerCase();
  return { inicio: "index", sobre: "sobre", projetos: "galeria", contato: "contato" }[id] || "index";
}

function setSpaNavActive(page) {
  document.querySelectorAll(".site-nav a, .mobile-nav a").forEach((a) => {
    const href = a.getAttribute("href") || "";
    let linkPage = "index";
    const hashMatch = href.match(/#(inicio|sobre|projetos|contato)\b/i);
    if (hashMatch) {
      linkPage = spaHashToPage(hashMatch[0]);
    } else if (/sobre\.html/i.test(href)) linkPage = "sobre";
    else if (/galeria\.html/i.test(href)) linkPage = "galeria";
    else if (/contato\.html/i.test(href)) linkPage = "contato";
    a.classList.toggle("is-active", linkPage === page);
  });
}

let spaNavBusy = false;
let spaScrollRaf = 0;

function easeInOutCubic(t) {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

/** Rolagem com easing (mais perceptível que o smooth nativo). */
function animateSpaScroll(toY, durationMs) {
  return new Promise((resolve) => {
    const fromY = window.pageYOffset;
    const dist = toY - fromY;
    if (Math.abs(dist) < 2) {
      resolve();
      return;
    }
    if (spaScrollRaf) {
      cancelAnimationFrame(spaScrollRaf);
      spaScrollRaf = 0;
    }
    const start = performance.now();
    const step = (now) => {
      const t = Math.min(1, (now - start) / durationMs);
      window.scrollTo(0, fromY + dist * easeInOutCubic(t));
      if (t < 1) {
        spaScrollRaf = requestAnimationFrame(step);
      } else {
        spaScrollRaf = 0;
        resolve();
      }
    };
    spaScrollRaf = requestAnimationFrame(step);
  });
}

function pulseSpaSection(el) {
  if (!el) return;
  el.classList.remove("is-spa-arrive");
  // reflow para reiniciar a animação
  void el.offsetWidth;
  el.classList.add("is-spa-arrive");
  const clear = () => el.classList.remove("is-spa-arrive");
  el.addEventListener("animationend", clear, { once: true });
  window.setTimeout(clear, 1100);
}

function scrollToSpaSection(hash, { updateHistory = true } = {}) {
  const id = String(hash || "").replace(/^#/, "");
  if (!id) return false;
  const el = document.getElementById(id);
  if (!el || el.hidden) return false;
  if (spaNavBusy) return false;

  spaNavBusy = true;

  const anchor =
    el.querySelector(".page-header, .page-cover__content, .about-grid, .contact-grid") || el;
  const header = document.querySelector(".site-header");
  const offset = (header ? header.getBoundingClientRect().height : 0) + 6;
  const top = Math.max(0, anchor.getBoundingClientRect().top + window.pageYOffset - offset);
  const distance = Math.abs(top - window.pageYOffset);
  const duration = Math.min(1100, Math.max(520, distance * 0.55));

  if (updateHistory) {
    const next = "#" + id;
    if (location.hash !== next) {
      history.replaceState(null, "", next);
    }
  }
  setSpaNavActive(spaHashToPage("#" + id));

  animateSpaScroll(top, duration).then(() => {
    pulseSpaSection(el);
    spaNavBusy = false;
  });

  return true;
}

function initOnePageNav() {
  if ((document.body.dataset.page || "") !== "index") return;

  document.addEventListener(
    "click",
    (event) => {
      const link = event.target.closest("a[href]");
      if (!link) return;
      if (event.button !== 0) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target !== "_self") return;
      if (link.hasAttribute("download")) return;

      let url;
      try {
        url = new URL(link.href, window.location.href);
      } catch (_) {
        return;
      }
      if (url.origin !== window.location.origin) return;
      const hash = url.hash || "";
      if (!/^#(inicio|sobre|projetos|contato)$/i.test(hash)) return;

      // Página única: âncoras do menu com rolagem com efeito
      event.preventDefault();
      event.stopPropagation();
      scrollToSpaSection(hash);
    },
    true,
  );

  const sections = ["inicio", "sobre", "projetos", "contato"]
    .map((id) => document.getElementById(id))
    .filter((el) => el && !el.hidden);

  if (sections.length && "IntersectionObserver" in window) {
    const observed = new Map();
    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          observed.set(entry.target.id, entry.isIntersecting ? entry.intersectionRatio : 0);
        });
        let bestId = "inicio";
        let bestRatio = 0;
        observed.forEach((ratio, id) => {
          if (ratio > bestRatio) {
            bestRatio = ratio;
            bestId = id;
          }
        });
        if (bestRatio > 0) setSpaNavActive(spaHashToPage("#" + bestId));
      },
      { rootMargin: "-20% 0px -55% 0px", threshold: [0, 0.25, 0.5, 0.75, 1] },
    );
    sections.forEach((el) => io.observe(el));
  }

  if (location.hash && /^#(inicio|sobre|projetos|contato)$/i.test(location.hash)) {
    requestAnimationFrame(() => {
      scrollToSpaSection(location.hash, { updateHistory: false });
    });
  } else {
    setSpaNavActive("index");
  }
}

function applyMotionPreference() {
  const settingOn = site("feature_animations") === "1";
  const prefersReduce =
    typeof window.matchMedia === "function" &&
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const on = settingOn && !prefersReduce;
  document.documentElement.classList.toggle("no-motion", !on);
  document.documentElement.dataset.animations = on ? "1" : "0";
}

function setupTestimonials() {
  const grid = document.getElementById("testimonials-grid");
  if (!grid) return;
  grid.innerHTML = "";
  let count = 0;
  for (let i = 1; i <= 3; i += 1) {
    const name = site(`testimonial_${i}_name`).trim();
    const text = site(`testimonial_${i}_text`).trim();
    if (!name || !text) continue;
    count += 1;
    const city = site(`testimonial_${i}_city`).trim();
    const li = document.createElement("li");
    li.className = "feature-card reveal";
    li.innerHTML = `
      <p>${escapeHtml(text)}</p>
      <h3 style="margin-top:1rem">${escapeHtml(name)}</h3>
      ${city ? `<p class="text-muted" style="margin:0.25rem 0 0">${escapeHtml(city)}</p>` : ""}`;
    grid.appendChild(li);
  }
  const section = document.querySelector("[data-section='testimonials']");
  if (section && count === 0) section.hidden = true;
}

function setupFaq() {
  const list = document.getElementById("faq-list");
  if (!list) return;
  list.innerHTML = "";
  let count = 0;
  for (let i = 1; i <= 4; i += 1) {
    const q = site(`faq_${i}_q`).trim();
    const a = site(`faq_${i}_a`).trim();
    if (!q || !a) continue;
    count += 1;
    const details = document.createElement("details");
    details.className = "faq-item reveal";
    details.innerHTML = `<summary>${escapeHtml(q)}</summary><p>${escapeHtml(a)}</p>`;
    list.appendChild(details);
  }
  const section = document.querySelector("[data-section='faq']");
  if (section && count === 0) section.hidden = true;
}

function setupContactForm() {
  const form = document.getElementById("contact-form");
  if (!form) return;

  let status = form.querySelector(".form-status");
  if (!status) {
    status = document.createElement("p");
    status.className = "form-status text-muted";
    status.setAttribute("role", "status");
    status.hidden = true;
    form.appendChild(status);
  }

  if (!form.querySelector('[name="website"]')) {
    const hp = document.createElement("input");
    hp.type = "text";
    hp.name = "website";
    hp.tabIndex = -1;
    hp.autocomplete = "off";
    hp.setAttribute("aria-hidden", "true");
    hp.style.cssText = "position:absolute;left:-9999px;opacity:0;height:0;width:0;";
    form.appendChild(hp);
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const nome = form.nome.value.trim();
    const mensagem = form.mensagem.value.trim();
    const website = (form.website && form.website.value) || "";
    const btn = form.querySelector('[type="submit"]');
    if (btn) btn.disabled = true;
    status.hidden = false;
    status.textContent = "Enviando…";
    status.classList.remove("form-status--error", "form-status--ok");

    // Honeypot: bots
    if (website) {
      status.textContent = "Mensagem enviada. Obrigado!";
      status.classList.add("form-status--ok");
      form.reset();
      if (btn) btn.disabled = false;
      return;
    }

    if (!nome || !mensagem) {
      status.textContent = "Preencha nome e mensagem.";
      status.classList.add("form-status--error");
      if (btn) btn.disabled = false;
      return;
    }

    const waDigits = String(site("whatsapp_number") || "").replace(/\D+/g, "");

    // Preferência: WhatsApp do site/lead (número publicado) — sem SMTP
    if (waDigits) {
      try {
        const brand = site("brand_name") || "site";
        const text =
          `Olá! Vim pelo site de ${brand}.\n\n` +
          `Nome: ${nome}\n` +
          `Mensagem: ${mensagem}`;
        const waUrl = `https://wa.me/${waDigits}?text=${encodeURIComponent(text)}`;
        status.textContent = "Abrindo WhatsApp…";
        status.classList.add("form-status--ok");
        window.open(waUrl, "_blank", "noopener,noreferrer");
        form.reset();
      } catch (err) {
        status.textContent = err.message || "Não foi possível abrir o WhatsApp.";
        status.classList.add("form-status--error");
      } finally {
        if (btn) btn.disabled = false;
      }
      return;
    }

    try {
      const res = await fetch(apiUrl("api/contact.php"), {
        method: "POST",
        headers: { Accept: "application/json", "Content-Type": "application/json" },
        body: JSON.stringify({ nome, mensagem, website }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) {
        throw new Error(data.error || "Não foi possível enviar.");
      }
      status.textContent = data.message || "Mensagem enviada. Obrigado!";
      status.classList.add("form-status--ok");
      form.reset();
    } catch (err) {
      status.textContent = err.message || "Falha no envio.";
      status.classList.add("form-status--error");
    } finally {
      if (btn) btn.disabled = false;
    }
  });
}

function initRevealAnimations() {
  if (site("feature_animations") === "0") {
    document.querySelectorAll(".reveal").forEach((el) => el.classList.add("is-visible"));
    return;
  }

  const targets = document.querySelectorAll(
    ".hero__grid > div, .hero__figure, .feature-card, .product-card, .stat-card, .channel-card, .contact-form, .about-figure, .about-content, .cta-section, .section-header, .section-header-row, .page-header, .info-box"
  );
  targets.forEach((el) => {
    // Preview vitrine: fade do título é controlado por is-faded-out (não pelo reveal)
    if (el.classList.contains("hero__copy") && el.closest('.hero[data-hero-preview="1"]')) {
      return;
    }
    if (!el.classList.contains("reveal")) el.classList.add("reveal");
  });

  const reveals = document.querySelectorAll(".reveal");
  if (!reveals.length) return;

  if (!("IntersectionObserver" in window)) {
    reveals.forEach((el) => el.classList.add("is-visible"));
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        io.unobserve(entry.target);
      });
    },
    { threshold: 0.12, rootMargin: "0px 0px -6% 0px" }
  );

  reveals.forEach((el, i) => {
    el.style.setProperty("--reveal-delay", `${Math.min(i * 70, 420)}ms`);
    io.observe(el);
  });
}

/** Preenche textos e links com data-site / data-site-href */
function applySiteTexts() {
  document.querySelectorAll("[data-site]").forEach((el) => {
    const key = el.getAttribute("data-site");
    if (!key) return;
    const value = site(key);
    if (el.tagName === "INPUT" || el.tagName === "TEXTAREA") {
      el.value = value;
    } else {
      el.textContent = value;
    }
  });

  document.querySelectorAll("[data-site-href]").forEach((el) => {
    let key = el.getAttribute("data-site-href");
    if (!key) return;
    // Corrigir absolutize antigo que virava data-site-href="/whatsapp"
    if (key === "/whatsapp" || key.endsWith("/whatsapp")) {
      key = "whatsapp";
      el.setAttribute("data-site-href", "whatsapp");
    }
    if (key === "whatsapp") {
      const wa = site("whatsapp_number").trim() ? whatsappUrl() : "";
      if (!wa) {
        el.hidden = true;
        el.setAttribute("aria-disabled", "true");
        el.removeAttribute("href");
        el.removeAttribute("target");
      } else {
        el.hidden = false;
        el.removeAttribute("aria-disabled");
        el.setAttribute("href", wa);
        el.setAttribute("target", "_blank");
        el.setAttribute("rel", "noopener noreferrer");
      }
    } else if (key === "email") {
      const mail = site("email").trim();
      if (mail) {
        el.setAttribute("href", `mailto:${mail}`);
        el.hidden = false;
      } else {
        el.removeAttribute("href");
        el.hidden = true;
      }
    } else if (key === "instagram") {
      const url = site("instagram_url").trim();
      if (url) {
        el.setAttribute("href", url);
        el.hidden = false;
      } else {
        el.removeAttribute("href");
        el.hidden = true;
      }
    } else if (key === "facebook") {
      const url = site("facebook_url").trim();
      if (url) {
        el.setAttribute("href", url);
        el.hidden = false;
      } else {
        el.removeAttribute("href");
        el.hidden = true;
      }
    } else if (key === "tiktok") {
      const url = site("tiktok_url").trim();
      if (url) {
        el.setAttribute("href", url);
        el.hidden = false;
      } else {
        el.removeAttribute("href");
        el.hidden = true;
      }
    } else if (key === "maps") {
      const url = site("maps_url").trim();
      if (url) {
        el.setAttribute("href", url);
        el.hidden = false;
      } else {
        el.removeAttribute("href");
      }
    } else {
      const url = site(key).trim();
      if (url) el.setAttribute("href", url);
      else el.removeAttribute("href");
    }
  });

  const hideChannel = (id, visible) => {
    const el = document.getElementById(id);
    if (!el) return;
    const wrap = el.closest("li") || el;
    wrap.hidden = !visible;
  };

  hideChannel("contact-whatsapp", Boolean(site("whatsapp_number").trim()));
  hideChannel("contact-email", Boolean(site("email").trim()));
  hideChannel("contact-instagram", Boolean(site("instagram_url").trim()));
  hideChannel("contact-facebook", Boolean(site("facebook_url").trim()));
  hideChannel("contact-tiktok", Boolean(site("tiktok_url").trim()));

  const address = site("address").trim();
  const maps = site("maps_url").trim();
  hideChannel("contact-address", Boolean(address || maps));

  const addrEl = document.getElementById("contact-address");
  if (addrEl) {
    if (maps) {
      addrEl.setAttribute("href", maps);
      addrEl.setAttribute("target", "_blank");
      addrEl.setAttribute("rel", "noreferrer");
      addrEl.classList.remove("channel-card--static");
      addrEl.onclick = null;
    } else if (address) {
      addrEl.removeAttribute("href");
      addrEl.removeAttribute("target");
      addrEl.removeAttribute("rel");
      addrEl.classList.add("channel-card--static");
      addrEl.onclick = (e) => e.preventDefault();
    } else {
      addrEl.removeAttribute("href");
      addrEl.onclick = null;
    }
  }

  const responseCard = document.getElementById("contact-response");
  if (responseCard) {
    const wrap = responseCard.closest("li") || responseCard;
    const title = site("contact_response_title").trim();
    const text = site("contact_response_text").trim();
    wrap.hidden = !(title || text);
  }

  // Labels de rede: se vazios, usa nome da plataforma
  const fillSocialLabel = (dataKey, fallback) => {
    document.querySelectorAll(`[data-site="${dataKey}"]`).forEach((el) => {
      if (!site(dataKey).trim()) el.textContent = fallback;
    });
  };
  fillSocialLabel("instagram_label", "Instagram");
  fillSocialLabel("facebook_label", "Facebook");
  fillSocialLabel("tiktok_label", "TikTok");
  if (!address && maps) {
    document.querySelectorAll('[data-site="address"]').forEach((el) => {
      el.textContent = "Ver no Google Maps";
    });
  }
}

function applyImages() {
  const favicon = document.querySelector('link[rel="icon"]');
  if (favicon) {
    if (IMAGES.favicon) {
      favicon.href = IMAGES.favicon;
      favicon.type = /\.ico(\?|$)/i.test(IMAGES.favicon)
        ? "image/x-icon"
        : /\.svg(\?|$)/i.test(IMAGES.favicon)
          ? "image/svg+xml"
          : /\.jpe?g(\?|$)/i.test(IMAGES.favicon)
            ? "image/jpeg"
            : "image/png";
    }
  }

  document.querySelectorAll("[data-img]").forEach((el) => {
    const key = el.getAttribute("data-img");
    // Só URLs da API — ausente → frame vazio (sem robô)
    const src = (key && IMAGES[key]) || "";
    el.setAttribute("referrerpolicy", "no-referrer");
    if (!src || isPlaceholderSrc(src)) {
      setMediaEmpty(el, true);
      return;
    }
    setMediaEmpty(el, false);
    el.setAttribute("src", src);
    el.classList.add("is-media-ready");
    attachImageFallback(el);
  });

  // Posters de vídeo acompanham o mesmo catálogo (não ficam com JPG antigo do HTML)
  document.querySelectorAll("[data-video]").forEach((video) => {
    const slug = video.getAttribute("data-video") || "";
    const posterMap = {
      "video-home": "hero",
      "video-sobre": "about",
      "video-galeria": "hero",
      "video-contato": "hero",
    };
    const imgKey = posterMap[slug] || "hero";
    const poster = IMAGES[imgKey] || "";
    if (poster && !isPlaceholderSrc(poster)) {
      video.setAttribute("poster", poster);
    } else {
      video.removeAttribute("poster");
    }
    video.classList.add("is-media-ready");
  });
}

function isVideoUrl(url) {
  return /\.(mp4|webm|ogg)(\?|#|$)/i.test(String(url || ""));
}

function motionAllowsMedia() {
  return (
    site("feature_animations") !== "0" &&
    !document.documentElement.classList.contains("no-motion") &&
    !window.matchMedia("(prefers-reduced-motion: reduce)").matches
  );
}

/** Preview playlist no hero — só na vitrine (não em sites de lead) */
function initHeroPreview() {
  const hero = document.querySelector('.hero[data-hero-preview="1"]');
  if (!hero) return;

  // Leads: hero padrão, sem som/play/menu/playlist
  if (isLeadSite()) {
    hero.setAttribute("data-hero-preview", "0");
    hero.removeAttribute("data-hero-playing");
    document.documentElement.classList.remove("is-hero-cinema");
    const controls = hero.querySelector(".hero__preview-controls");
    if (controls) controls.hidden = true;
    const preview = hero.querySelector(".hero__preview");
    if (preview) {
      preview.setAttribute("aria-hidden", "true");
      const vid = preview.querySelector("video");
      if (vid) {
        vid.pause();
        vid.removeAttribute("src");
        try {
          vid.load();
        } catch (_) {
          /* ignore */
        }
      }
    }
    return;
  }

  const video = hero.querySelector(".hero__preview-video");
  const soundBtn = hero.querySelector(".hero__preview-sound");
  const playBtn = hero.querySelector(".hero__preview-play");
  const menuBtn = hero.querySelector(".hero__preview-menu");
  const copy = hero.querySelector(".hero__copy");
  if (!video || !playBtn) return;

  // Evita conflito com .reveal { opacity: 1 !important } em no-motion
  if (copy) {
    copy.classList.remove("reveal", "is-visible");
  }

  const playlist = [
    "assets/hero-preview-2.mp4",
    "assets/hero-preview-3.mp4",
    "assets/hero-preview-4.mp4",
    "assets/hero-preview-1.mp4",
  ];

  let trackIndex = 0;
  let soundArmed = false;
  let isPlaying = false;
  let restoreTimer = 0;

  const setPulse = (el, on) => {
    if (!el) return;
    if (on) el.setAttribute("data-pulse", "1");
    else el.removeAttribute("data-pulse");
  };

  const setHeaderVisible = (visible) => {
    document.documentElement.classList.toggle("is-hero-cinema", !visible);
    const header = document.querySelector(".site-header");
    if (header) {
      header.classList.toggle("is-faded-out", !visible);
      header.setAttribute("aria-hidden", visible ? "false" : "true");
    }
    if (menuBtn && !menuBtn.hidden) {
      menuBtn.setAttribute("aria-expanded", visible ? "true" : "false");
      menuBtn.setAttribute("aria-label", visible ? "Ocultar menu" : "Mostrar menu");
      menuBtn.title = visible ? "Ocultar menu" : "Menu";
    }
  };

  const setTitleVisible = (visible) => {
    if (copy) {
      copy.classList.toggle("is-faded-out", !visible);
      copy.setAttribute("aria-hidden", visible ? "false" : "true");
    }
    // Hamburger só some quando o título reaparece
    if (menuBtn) menuBtn.hidden = visible;
    if (visible && restoreTimer) {
      window.clearTimeout(restoreTimer);
      restoreTimer = 0;
    }
  };

  const enterCinema = () => {
    setTitleVisible(false);
    setHeaderVisible(false);
  };

  const restoreTitleAndMenu = () => {
    setHeaderVisible(true);
    setTitleVisible(true);
  };

  const syncSoundBtn = () => {
    if (!soundBtn) return;
    const muted = !soundArmed || !!video.muted;
    soundBtn.setAttribute("aria-pressed", muted ? "false" : "true");
    soundBtn.setAttribute("aria-label", muted ? "Ativar som" : "Mutar");
    soundBtn.title = muted ? "Ativar som" : "Mutar";
  };

  const loadTrack = (index) => {
    trackIndex = ((index % playlist.length) + playlist.length) % playlist.length;
    video.src = playlist[trackIndex];
    try {
      video.load();
    } catch (_) {
      /* ignore */
    }
  };

  const playCurrent = () => {
    video.muted = !soundArmed;
    const playPromise = video.play();
    if (playPromise && typeof playPromise.catch === "function") {
      return playPromise.catch(() => {
        video.muted = true;
        soundArmed = false;
        setPulse(soundBtn, true);
        syncSoundBtn();
        return video.play().catch(() => {});
      });
    }
    return Promise.resolve();
  };

  const crossfadeToNext = () => {
    video.classList.add("is-crossfading");
    window.setTimeout(() => {
      loadTrack(trackIndex + 1);
      const reveal = () => {
        video.removeEventListener("playing", reveal);
        video.classList.remove("is-crossfading");
      };
      video.addEventListener("playing", reveal);
      playCurrent().finally(() => {
        // Fallback se "playing" não disparar
        window.setTimeout(reveal, 400);
      });
    }, 500);
  };

  const startPlayback = () => {
    if (isPlaying) return;
    isPlaying = true;
    hero.setAttribute("data-hero-playing", "1");
    hero.querySelector(".hero__preview")?.removeAttribute("aria-hidden");
    playBtn.hidden = true;
    setPulse(playBtn, false);
    setPulse(soundBtn, !soundArmed);
    enterCinema();

    video.classList.remove("is-crossfading");
    loadTrack(0);
    playCurrent();
    syncSoundBtn();
  };

  // Idle: fundo nítido + caixa + pulse no som
  video.removeAttribute("src");
  video.pause();
  restoreTitleAndMenu();
  setPulse(soundBtn, true);
  setPulse(playBtn, false);
  syncSoundBtn();

  if (soundBtn) {
    soundBtn.addEventListener("click", () => {
      if (!isPlaying) {
        soundArmed = !soundArmed;
        video.muted = !soundArmed;
        setPulse(soundBtn, !soundArmed);
        setPulse(playBtn, soundArmed);
        syncSoundBtn();
        return;
      }

      const wantSound = video.muted;
      video.muted = !wantSound;
      soundArmed = !video.muted;
      if (!video.muted) {
        setPulse(soundBtn, false);
        video.play().catch(() => {
          video.muted = true;
          soundArmed = false;
          setPulse(soundBtn, true);
          syncSoundBtn();
        });
      } else {
        setPulse(soundBtn, true);
      }
      syncSoundBtn();
    });
  }

  playBtn.addEventListener("click", () => {
    startPlayback();
  });

  if (menuBtn) {
    menuBtn.addEventListener("click", () => {
      // Toggle: abre/fecha o menu do site; ☰ só some quando o título volta
      const headerOpen = !document.documentElement.classList.contains("is-hero-cinema");
      setHeaderVisible(!headerOpen);
    });
  }

  video.addEventListener("ended", () => {
    if (!isPlaying) return;
    // Após o arquivo 4 (3º da sequência 2→3→4→1), título volta com 1s de atraso
    const finishedFour = /hero-preview-4\.mp4(?:$|\?)/.test(playlist[trackIndex] || "");
    if (finishedFour) {
      restoreTimer = window.setTimeout(() => {
        restoreTimer = 0;
        restoreTitleAndMenu();
      }, 1000);
    }
    crossfadeToNext();
  });
}

/** Vídeo de capa — só no look Xhybrid Signature */
function applyVideos() {
  const isSignature =
    document.documentElement.getAttribute("data-look") === "xhybrid-signature";
  const canPlay = isSignature && motionAllowsMedia();

  const firstGallerySrc = () => {
    if (Array.isArray(galleryPhotos) && galleryPhotos.length) {
      const src = galleryPhotos[0].src || galleryPhotos[0].url || "";
      if (src && !isVideoUrl(src)) return src;
    }
    const skip = new Set([
      "favicon",
      "logo",
      "video-home",
      "video-sobre",
      "video-galeria",
      "video-contato",
    ]);
    for (const key of Object.keys(IMAGES)) {
      if (skip.has(key) || String(key).startsWith("video-")) continue;
      const src = IMAGES[key];
      if (src && !isVideoUrl(src)) return src;
    }
    return IMAGES.hero || "";
  };

  document.querySelectorAll("[data-video]").forEach((video) => {
    const slug = video.getAttribute("data-video");
    const src = (slug && IMAGES[slug]) || "";
    const media = video.closest(".hero__media, .page-cover__media");
    const fallback = media ? media.querySelector("img") : null;

    if (fallback && slug === "video-galeria") {
      const poster = firstGallerySrc() || "";
      if (poster && !isPlaceholderSrc(poster)) {
        setMediaEmpty(fallback, false);
        fallback.setAttribute("src", poster);
        video.setAttribute("poster", poster);
      } else {
        setMediaEmpty(fallback, true);
        video.removeAttribute("poster");
      }
    }

    const showFallback = () => {
      video.removeAttribute("src");
      try {
        video.load();
      } catch (_) {
        /* ignore */
      }
      video.hidden = true;
      if (fallback) {
        // Mantém o estado já definido por applyImages (imagem real ou frame vazio)
        if (!fallback.classList.contains("is-media-empty")) {
          fallback.hidden = false;
        }
      }
    };

    if (!canPlay || !isVideoUrl(src)) {
      showFallback();
      return;
    }

    video.hidden = false;
    if (fallback) fallback.hidden = true;
    video.muted = true;
    video.loop = true;
    video.playsInline = true;
    video.setAttribute("playsinline", "");
    video.src = src;
    const playPromise = video.play();
    if (playPromise && typeof playPromise.catch === "function") {
      playPromise.catch(() => showFallback());
    }
  });
}

/** Transição de saída entre páginas HTML (Signature + animações on) */
function setupPageTransitions() {
  const isSignature =
    document.documentElement.getAttribute("data-look") === "xhybrid-signature";
  if (!isSignature || !motionAllowsMedia()) return;

  const page = document.querySelector(".page");
  if (!page) return;

  document.addEventListener(
    "click",
    (event) => {
      const link = event.target.closest("a[href]");
      if (!link) return;
      if (event.defaultPrevented || event.button !== 0) return;
      if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
      if (link.target && link.target !== "_self") return;
      if (link.hasAttribute("download")) return;
      if (link.hasAttribute("data-site-href")) return;
      if (link.id === "hero-whatsapp" || link.id === "cta-whatsapp") return;
      if (link.classList.contains("fab-whatsapp")) return;

      let url;
      try {
        url = new URL(link.href, window.location.href);
      } catch (_) {
        return;
      }
      if (url.origin !== window.location.origin) return;
      if (url.protocol === "https:" && /wa\.me/i.test(url.hostname)) return;
      // Âncoras da página única: não dispara transição de saída
      if (/^#(inicio|sobre|projetos|contato)$/i.test(url.hash || "")) return;
      if (!/\.html?$/i.test(url.pathname) && !/\/$/.test(url.pathname)) return;
      if (
        url.pathname === window.location.pathname &&
        url.search === window.location.search &&
        !url.hash
      ) {
        return;
      }
      if (url.hash && url.pathname === window.location.pathname) return;

      event.preventDefault();
      page.classList.add("is-leaving");
      window.setTimeout(() => {
        window.location.href = url.href;
      }, 280);
    },
    true,
  );
}

/** Resolve api/*.php a partir da pasta do site (funciona em subpasta) */
function apiUrl(file) {
  const scripts = document.getElementsByTagName("script");
  for (let i = scripts.length - 1; i >= 0; i -= 1) {
    const src = scripts[i].getAttribute("src") || "";
    if (!src) continue;
    if (src.includes("js/main.js") || /\/main\.js(\?|$)/.test(src)) {
      try {
        return new URL("../" + file.replace(/^\//, ""), scripts[i].src || src).href;
      } catch (_) {
        /* fall through */
      }
    }
  }
  return file.replace(/^\//, "");
}

/** Path público de lead: /{slug}/{letra}{id}  ex.: /eletricistaton/x22 */
function parsePublishedLeadPath() {
  try {
    if (window.__xhybridLeadPath && typeof window.__xhybridLeadPath === "object") {
      const p = window.__xhybridLeadPath;
      if (p.slug && p.code && p.leadId) {
        return {
          slug: String(p.slug).toLowerCase(),
          code: String(p.code).toLowerCase(),
          leadId: Number(p.leadId) || 0,
        };
      }
    }
    let path = window.location.pathname || "";
    const base = String(window.__xhybridAppBase || "").replace(/\/$/, "");
    if (base && (path === base || path.startsWith(base + "/"))) {
      path = path.slice(base.length) || "/";
    }
    // Novo: /slug/x22
    let m = path.match(/^\/([a-z0-9]+(?:-[a-z0-9]+)*)\/([a-z])(\d+)(?:\/|$)/i);
    if (m) {
      return {
        slug: m[1].toLowerCase(),
        code: m[2].toLowerCase(),
        leadId: Number(m[3]) || 0,
      };
    }
    // Legado: /slug/22x
    m = path.match(/^\/([a-z0-9]+(?:-[a-z0-9]+)*)\/(\d+)([a-z])(?:\/|$)/i);
    if (!m) return null;
    return {
      slug: m[1].toLowerCase(),
      leadId: Number(m[2]) || 0,
      code: m[3].toLowerCase(),
    };
  } catch (_) {
    return null;
  }
}

async function fetchPublishedOverlay(lead) {
  const url =
    apiUrl("api/published_site.php") +
    "?slug=" +
    encodeURIComponent(lead.slug) +
    "&code=" +
    encodeURIComponent(lead.code) +
    "&lead_id=" +
    encodeURIComponent(String(lead.leadId || ""));
  const res = await fetch(url, {
    headers: { Accept: "application/json" },
    cache: "no-store",
  });
  if (!res.ok) throw new Error("published " + res.status);
  const data = await res.json();
  if (!data || typeof data !== "object" || data.error || !data.settings) {
    throw new Error("published inválido");
  }
  return data.settings;
}

/** Reescreve links .html do HTML estático para âncoras da página única (e path do lead). */
function rewriteLeadInternalLinks() {
  const hashToFile = {
    inicio: "index.html",
    sobre: "sobre.html",
    projetos: "galeria.html",
    contato: "contato.html",
  };

  document.querySelectorAll("a[href]").forEach((a) => {
    // Nunca tocar CTAs externos (WhatsApp/orçamento, mailto, etc.)
    if (a.hasAttribute("data-site-href")) return;
    if (a.id === "hero-whatsapp" || a.id === "cta-whatsapp") return;
    if (a.classList.contains("fab-whatsapp")) return;
    const href = a.getAttribute("href") || "";
    if (!href || /^(https?:|mailto:|tel:|\/\/)/i.test(href)) return;
    if (/wa\.me/i.test(href)) return;

    let file = "";
    const spaHash = href.match(/#(inicio|sobre|projetos|contato)\b/i);
    if (spaHash) {
      // Já tem âncora SPA — preservar a seção (não cair em index.html → #inicio)
      file = hashToFile[spaHash[1].toLowerCase()] || "index.html";
    } else {
      const clean = href.replace(/^\.\//, "").replace(/^\//, "");
      if (!/\.html(?:[?#].*)?$/i.test(clean)) return;
      file = clean.split(/[?#]/)[0] || "index.html";
      if (!PAGE_SECTION_HASH[file]) return;
    }

    const next = leadPageHref(file);
    if (next && a.getAttribute("href") !== next) {
      a.setAttribute("href", next);
    }
  });
}

/** Garante href wa.me nos botões de orçamento (nunca path do lead / vazio). */
function bindQuoteWhatsAppButtons() {
  syncContactGlobals();
  // Preferir número do lead publicado quando existir
  if (window.__xhybridLeadSettings && typeof window.__xhybridLeadSettings === "object") {
    const leadWa = window.__xhybridLeadSettings.whatsapp_number;
    if (typeof leadWa === "string" && leadWa.trim() !== "") {
      SITE.whatsapp_number = leadWa.trim();
    }
    const leadMsg = window.__xhybridLeadSettings.whatsapp_message;
    if (typeof leadMsg === "string" && leadMsg.trim() !== "") {
      SITE.whatsapp_message = leadMsg;
    }
    syncContactGlobals();
  }
  const url = site("whatsapp_number").trim() ? whatsappUrl() : "";
  document.querySelectorAll('[data-site-href="whatsapp"], [data-site-href="/whatsapp"], #hero-whatsapp, #cta-whatsapp').forEach((el) => {
    if (el.getAttribute("data-site-href") === "/whatsapp") {
      el.setAttribute("data-site-href", "whatsapp");
    }
    if (!url) {
      el.hidden = true;
      el.setAttribute("aria-disabled", "true");
      el.removeAttribute("href");
      el.removeAttribute("target");
      el.onclick = (e) => e.preventDefault();
      return;
    }
    el.hidden = false;
    el.removeAttribute("aria-disabled");
    el.onclick = null;
    el.setAttribute("href", url);
    el.setAttribute("target", "_blank");
    el.setAttribute("rel", "noopener noreferrer");
    // Ícone de orçamento (bolsa), não balão de WhatsApp
    const iconHost = el.querySelector("svg");
    if (iconHost && ICONS.quote) {
      const wrap = document.createElement("span");
      wrap.innerHTML = ICONS.quote;
      const next = wrap.firstElementChild;
      if (next) {
        iconHost.replaceWith(next);
      }
    }
  });
  const fab = document.querySelector(".fab-whatsapp");
  if (fab) {
    if (url) {
      fab.setAttribute("href", url);
      fab.hidden = false;
    } else {
      fab.hidden = true;
      fab.removeAttribute("href");
    }
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  let apiRows = [];
  let apiOk = false;
  const leadPath = parsePublishedLeadPath();
  window.__xhybridLeadPath = leadPath || window.__xhybridLeadPath || null;

  function revealLeadPage() {
    document.documentElement.classList.remove("lead-booting");
  }

  function paintLeadShell() {
    syncContactGlobals();
    const page = document.body.dataset.page || "index";
    setupLayout(page);
    rewriteLeadInternalLinks();
    applyBrandMeta();
    applySiteTexts();
    bindQuoteWhatsAppButtons();
    if (typeof applyFeatureIcons === "function") {
      applyFeatureIcons();
    }
    applySections();
    revealLeadPage();
  }

  // Lead: overlay do router já no HTML — pinta textos certos e revela (sem flash "Projetos")
  if (leadPath && window.__xhybridLeadSettings && typeof window.__xhybridLeadSettings === "object") {
    applySiteSettings(window.__xhybridLeadSettings);
    paintLeadShell();
  }

  const [imagesResult, settingsResult, servicesResult] = await Promise.allSettled([
    fetch(
      apiUrl("api/images.php") +
        (leadPath && leadPath.leadId
          ? "?" +
            new URLSearchParams({
              lead_id: String(leadPath.leadId),
              slug: String(leadPath.slug || ""),
              code: String(leadPath.code || ""),
            }).toString()
          : ""),
      {
        headers: { Accept: "application/json" },
        cache: "no-store",
      },
    ).then(async (res) => {
      if (!res.ok) throw new Error("API " + res.status);
      const data = await res.json();
      if (!Array.isArray(data)) throw new Error("JSON inválido");
      return data;
    }),
    fetch(apiUrl("api/settings.php"), {
      headers: { Accept: "application/json" },
      cache: "no-store",
    }).then(async (res) => {
      if (!res.ok) throw new Error("API " + res.status);
      const data = await res.json();
      if (!data || typeof data !== "object" || data.error) {
        throw new Error("Settings inválido");
      }
      return data;
    }),
    fetch(
      apiUrl("api/services.php") +
        (leadPath && leadPath.leadId
          ? "?" +
            new URLSearchParams({
              lead_id: String(leadPath.leadId),
              slug: String(leadPath.slug || ""),
              code: String(leadPath.code || ""),
            }).toString()
          : ""),
      {
        headers: { Accept: "application/json" },
        cache: "no-store",
      },
    ).then(async (res) => {
      if (!res.ok) throw new Error("API " + res.status);
      const data = await res.json();
      if (!Array.isArray(data)) throw new Error("Services inválido");
      return data;
    }),
  ]);

  if (imagesResult.status === "fulfilled") {
    apiRows = imagesResult.value;
    apiOk = true;
    applyImageCatalog(apiRows);
  } else {
    applyFallbackCatalog();
  }

  // Vitrine: settings da agência. Lead: não misturar agência (evita flash Xhybrid/"Projetos")
  if (!leadPath && settingsResult.status === "fulfilled") {
    applySiteSettings(settingsResult.value);
  }

  if (leadPath) {
    if (window.__xhybridLeadSettings && typeof window.__xhybridLeadSettings === "object") {
      applySiteSettings(window.__xhybridLeadSettings);
    } else {
      try {
        const overlay = await fetchPublishedOverlay(leadPath);
        applySiteSettings(overlay);
      } catch (_) {
        /* sem overlay */
      }
      paintLeadShell();
    }
  }

  syncContactGlobals();

  if (servicesResult.status === "fulfilled") {
    applyServicesCatalog(servicesResult.value);
  }

  bindProductImages();
  rebuildGalleryPhotos(apiRows, apiOk);
  applyImages();
  applyBrandMeta();
  applyAnalytics();
  applyMotionPreference();
  enforcePlanPages();

  const page = document.body.dataset.page || "index";
  setupLayout(page);
  rewriteLeadInternalLinks();
  applySiteTexts();
  bindQuoteWhatsAppButtons();
  document.querySelectorAll(".site-logo__img, .product-card img, .gallery__item img").forEach((img) => {
    attachImageFallback(img);
  });
  applyVideos();
  initHeroPreview();
  applyImages();
  applySiteTexts();
  bindQuoteWhatsAppButtons();
  if (typeof applyFeatureIcons === "function") {
    applyFeatureIcons();
  }
  applySections();
  setupHomeDestaques();
  setupTestimonials();
  setupFaq();
  setupGallery();
  setupContactForm();
  initRevealAnimations();
  setupPageTransitions();
  initOnePageNav();
  bindQuoteWhatsAppButtons();
  revealLeadPage();
});
