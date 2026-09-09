/* SVG icons (Lucide-style) */
const ICONS = {
  sparkles: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/></svg>',
  arrowRight: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>',
  heart: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>',
  messageCircle: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>',
  menu: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>',
  x: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>',
  palette: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>',
  check: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>',
  instagram: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>',
  mail: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>',
  clock: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  send: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>',
  whatsapp: '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.17-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.07-.15-.67-1.61-.91-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48s1.07 2.88 1.21 3.08c.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35M12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.44 9.88-9.89 9.88m8.41-18.3A11.82 11.82 0 0 0 12.05 0C5.5 0 .16 5.33.16 11.89c0 2.1.55 4.14 1.59 5.94L.06 24l6.3-1.65a11.9 11.9 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.33 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41"/></svg>',
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
  return typeof v === "string" && LOOK_PRESETS.some((l) => l.id === v);
}

function getLookPreset(lookId) {
  return LOOK_PRESETS.find((l) => l.id === lookId) || LOOK_PRESETS[0];
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
  return {
    look: preset.id,
    theme: preset.theme,
    font: preset.font,
    layout: preset.layout,
    media: preset.media,
  };
}

function applyAppearance(appearance, { persist = true } = {}) {
  const look = isLookId(appearance.look) ? appearance.look : DEFAULT_LOOK;
  const preset = getLookPreset(look);
  const theme = preset.theme;
  const font = preset.font;
  const layout = preset.layout;
  const media = preset.media;

  applyLook(look);
  applyTheme(theme);
  applyFont(font);
  applyLayout(layout);
  applyMedia(media);

  if (persist) cacheAppearance(theme, font, layout, media, look);

  return { look, theme, font, layout, media };
}

function setupAppearancePreviewListener() {
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

function renderHeader(currentPage) {
  const navLinks = getNavLinks().map((l) => {
    const active = l.page === currentPage ? " is-active" : "";
    return `<a href="${l.href}" class="${active.trim()}">${escapeHtml(l.label)}</a>`;
  }).join("");

  const mobileLinks = getNavLinks().map((l) => {
    const active = l.page === currentPage ? " is-active" : "";
    return `<li><a href="${l.href}" class="${active.trim()}">${escapeHtml(l.label)}</a></li>`;
  }).join("");

  return `
    <header class="site-header">
      <div class="container site-header__inner">
        <a href="index.html" class="site-logo" aria-label="Xhybrid — página inicial">
          X<span class="text-primary italic">hybrid</span>
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
  const wa = whatsappUrl();
  const ig = site("instagram_url");
  const mail = site("email");
  const hoursTitle = site("footer_hours_title");
  const hoursLines = ["footer_hours_line1", "footer_hours_line2", "footer_hours_line3"]
    .map((key) => site(key).trim())
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

  return `
    <footer class="site-footer">
      <div class="container site-footer__grid">
        <div>
          <p class="site-footer__brand">X<span class="text-primary italic">hybrid</span></p>
          <p class="site-footer__tagline">${escapeHtml(site("footer_tagline"))}</p>
        </div>
        <nav aria-label="Links do rodapé">
          <p class="site-footer__heading">Navegue</p>
          <ul class="site-footer__links">
            <li><a href="sobre.html">${escapeHtml(site("footer_link_sobre"))}</a></li>
            <li><a href="galeria.html">${escapeHtml(site("nav_galeria"))}</a></li>
            <li><a href="contato.html">${escapeHtml(site("nav_contato"))}</a></li>
          </ul>
        </nav>
        <div>
          <p class="site-footer__heading">Fale com a gente</p>
          <div class="social-links">
            <a href="${escapeHtml(wa)}" target="_blank" rel="noreferrer" aria-label="WhatsApp da Xhybrid">${ICONS.messageCircle}</a>
            <a href="${escapeHtml(ig)}" target="_blank" rel="noreferrer" aria-label="Instagram da Xhybrid">${ICONS.instagram}</a>
            <a href="mailto:${escapeHtml(mail)}" aria-label="Enviar e-mail para a Xhybrid">${ICONS.mail}</a>
          </div>
          <p class="site-footer__tagline" style="margin-top:0.75rem">${escapeHtml(mail)}</p>
        </div>
        ${hoursBlock}
      </div>
      <div class="site-footer__bottom">
        © ${year} Xhybrid — criação de sites, manutenção e tecnologia.
      </div>
    </footer>`;
}

function renderFabWhatsApp() {
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

  applyAppearance(resolveAppearance());
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

  products
    .filter((p) => p.imagem)
    .slice(0, 3)
    .forEach((p) => {
      const li = document.createElement("li");
      li.className = "product-card product-card--bordered reveal";
      li.innerHTML = `
      <div class="product-card__img-wrap">
        <img src="${p.imagem}" alt="${escapeHtml(p.nome)}" width="1024" height="768" loading="lazy" referrerpolicy="no-referrer">
      </div>
      <div class="product-card__body">
        <p class="product-card__category">${escapeHtml(p.categoria)}</p>
        <h3 class="product-card__title">${escapeHtml(p.nome)}</h3>
      </div>`;
      grid.appendChild(li);
      attachDriveFallback(li.querySelector("img"));
    });
}

function setupContactForm() {
  const form = document.getElementById("contact-form");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const nome = form.nome.value.trim();
    const mensagem = form.mensagem.value.trim();
    const assunto = encodeURIComponent(`Contato do site — ${nome}`);
    const corpo = encodeURIComponent(`Olá! Sou ${nome}.\n\n${mensagem}`);
    window.location.href = `mailto:${site("email")}?subject=${assunto}&body=${corpo}`;
  });
}

function initRevealAnimations() {
  const targets = document.querySelectorAll(
    ".hero__grid > div, .hero__figure, .feature-card, .product-card, .stat-card, .channel-card, .contact-form, .about-figure, .about-content, .cta-section, .section-header, .section-header-row, .page-header, .info-box"
  );
  targets.forEach((el) => {
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
    const key = el.getAttribute("data-site-href");
    if (!key) return;
    if (key === "whatsapp") {
      el.setAttribute("href", whatsappUrl());
    } else if (key === "email") {
      el.setAttribute("href", `mailto:${site("email")}`);
    } else if (key === "instagram") {
      el.setAttribute("href", site("instagram_url"));
    } else {
      el.setAttribute("href", site(key));
    }
  });
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
    const src = IMAGES[key];
    el.setAttribute("referrerpolicy", "no-referrer");
    if (src) {
      el.hidden = false;
      el.setAttribute("src", src);
      attachDriveFallback(el);
    } else {
      el.removeAttribute("src");
      el.hidden = true;
    }
  });
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

document.addEventListener("DOMContentLoaded", async () => {
  let apiRows = [];
  let apiOk = false;

  const [imagesResult, settingsResult] = await Promise.allSettled([
    fetch(apiUrl("api/images.php"), {
      headers: { Accept: "application/json" },
      cache: "no-store",
    }).then(async (res) => {
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
  ]);

  if (imagesResult.status === "fulfilled") {
    apiRows = imagesResult.value;
    apiOk = true;
    applyImageCatalog(apiRows);
  } else {
    applyFallbackCatalog();
  }

  if (settingsResult.status === "fulfilled") {
    applySiteSettings(settingsResult.value);
  }
  syncContactGlobals();

  bindProductImages();
  rebuildGalleryPhotos(apiRows, apiOk);
  applyImages();

  const page = document.body.dataset.page || "index";
  setupLayout(page);
  applySiteTexts();
  setupHomeDestaques();
  setupGallery();
  setupContactForm();
  initRevealAnimations();
});
