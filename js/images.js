/**
 * Helpers de imagem (Google Drive) e mapa preenchido pela API.
 * As URLs vêm de /api/images.php (SQLite). IMAGE_FALLBACKS só se a API falhar.
 */

/** Placeholder embutido (data-URI) — não depende de arquivo no disco / path do admin */
const IMAGE_PLACEHOLDER =
  "data:image/svg+xml;charset=utf-8," +
  encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 132" fill="none">' +
      '<defs>' +
      '<linearGradient id="phBody" x1="20%" y1="0%" x2="80%" y2="100%"><stop offset="0%" stop-color="#2a2a2a"/><stop offset="100%" stop-color="#111111"/></linearGradient>' +
      '<linearGradient id="phGlass" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#ffffff" stop-opacity=".16"/><stop offset="100%" stop-color="#ffffff" stop-opacity=".02"/></linearGradient>' +
      "</defs>" +
      '<rect width="120" height="132" rx="10" fill="#0a0a0a"/>' +
      '<ellipse cx="60" cy="122" rx="22" ry="4" fill="#fff" opacity=".12"/>' +
      '<line x1="60" y1="28" x2="60" y2="18" stroke="#fff" stroke-opacity=".5" stroke-width="2" stroke-linecap="round"/>' +
      '<circle cx="60" cy="15" r="4" fill="#c8c8c8"/>' +
      '<circle cx="60" cy="15" r="1.6" fill="#fff" opacity=".9"/>' +
      '<rect x="34" y="30" width="52" height="36" rx="10" fill="url(#phBody)" stroke="#fff" stroke-opacity=".2" stroke-width="1.5"/>' +
      '<rect x="34" y="30" width="52" height="36" rx="10" fill="url(#phGlass)"/>' +
      '<rect x="42" y="40" width="36" height="16" rx="5" fill="#050505" stroke="#fff" stroke-opacity=".12"/>' +
      '<rect x="47" y="44" width="8" height="8" rx="2" fill="#e8e8e8"/>' +
      '<rect x="49" y="46" width="3" height="3" rx="0.8" fill="#fff" opacity=".95"/>' +
      '<rect x="65" y="44" width="8" height="8" rx="2" fill="#e8e8e8"/>' +
      '<rect x="67" y="46" width="3" height="3" rx="0.8" fill="#fff" opacity=".95"/>' +
      '<path d="M52 58h16" stroke="#fff" stroke-opacity=".35" stroke-width="2" stroke-linecap="round"/>' +
      '<rect x="54" y="66" width="12" height="6" rx="2" fill="#1a1a1a" stroke="#fff" stroke-opacity=".12"/>' +
      '<rect x="38" y="72" width="44" height="32" rx="8" fill="url(#phBody)" stroke="#fff" stroke-opacity=".18" stroke-width="1.5"/>' +
      '<rect x="38" y="72" width="44" height="32" rx="8" fill="url(#phGlass)"/>' +
      '<text x="60" y="94" text-anchor="middle" font-family="Segoe UI, Arial, sans-serif" font-size="14" font-weight="700" fill="#fff" fill-opacity=".88">X</text>' +
      '<circle cx="48" cy="80" r="2" fill="#d0d0d0" opacity=".75"/>' +
      '<circle cx="60" cy="80" r="2" fill="#fff" opacity=".35"/>' +
      '<circle cx="72" cy="80" r="2" fill="#fff" opacity=".2"/>' +
      '<rect x="24" y="76" width="12" height="6" rx="3" fill="#1c1c1c" stroke="#fff" stroke-opacity=".15"/>' +
      '<circle cx="23" cy="79" r="4" fill="#2a2a2a" stroke="#fff" stroke-opacity=".2"/>' +
      '<rect x="84" y="76" width="12" height="6" rx="3" fill="#1c1c1c" stroke="#fff" stroke-opacity=".15"/>' +
      '<circle cx="97" cy="79" r="4" fill="#2a2a2a" stroke="#fff" stroke-opacity=".2"/>' +
      '<rect x="46" y="104" width="10" height="12" rx="3" fill="#1a1a1a" stroke="#fff" stroke-opacity=".12"/>' +
      '<rect x="64" y="104" width="10" height="12" rx="3" fill="#1a1a1a" stroke="#fff" stroke-opacity=".12"/>' +
      '<rect x="18" y="48" width="4" height="4" rx="1" fill="#bbb" opacity=".55"/>' +
      '<rect x="98" y="38" width="3" height="3" rx="0.8" fill="#bbb" opacity=".45"/>' +
      '<rect x="16" y="88" width="3" height="3" rx="0.6" fill="#bbb" opacity=".35"/>' +
      "</svg>",
  );

function driveFileId(url) {
  if (!url) return "";
  const text = String(url).trim();
  const match =
    text.match(/\/d\/([a-zA-Z0-9_-]+)/) ||
    text.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  return match ? match[1] : "";
}

function driveToSrc(url) {
  if (!url) return "";
  const text = String(url).trim();
  if (!text) return "";

  const id = driveFileId(text);
  if (id) {
    return `https://lh3.googleusercontent.com/d/${id}=w2000`;
  }
  return text;
}

function imageSrc(url, fallback) {
  return driveToSrc(url) || fallback || IMAGE_PLACEHOLDER;
}

function isPlaceholderSrc(src) {
  const s = String(src || "");
  return (
    /placeholder-robot\.svg(\?|$)/i.test(s) ||
    /^data:image\/svg\+xml/i.test(s)
  );
}

/**
 * Fallback em cascata: Drive thumbnail → robô placeholder.
 * Compat: attachDriveFallback mantido como alias.
 */
function attachImageFallback(img) {
  if (!img || img.dataset.fallbackBound === "1") return;
  img.dataset.fallbackBound = "1";
  img.addEventListener("error", function onErr() {
    const id = driveFileId(img.currentSrc || img.src);
    if (id && !/thumbnail\?id=/.test(img.src)) {
      img.src = `https://drive.google.com/thumbnail?id=${id}&sz=w2000`;
      return;
    }
    if (!isPlaceholderSrc(img.src)) {
      img.src = IMAGE_PLACEHOLDER;
      img.classList.add("is-placeholder");
    }
  });
}

function attachDriveFallback(img) {
  attachImageFallback(img);
}

/** Fallbacks locais — só marca (logo/favicon) se a API falhar.
 *  Hero/about/galeria NÃO voltam por fallback: imagem desativada no admin
 *  deve sumir do site (vira robô via IMAGE_PLACEHOLDER).
 */
const IMAGE_FALLBACKS = {
  favicon: "favicon.svg",
  logo: "assets/logo-xhybrid.svg",
};

/** Preenchido em runtime a partir da API (só imagens ativas) */
const IMAGES = {};

/**
 * @param {Array} rows - imagens ativas da API
 * @param {{ fillMissingFallbacks?: boolean }} options
 *   fillMissingFallbacks=true só quando a API estiver indisponível
 */
function applyImageCatalog(rows, options = {}) {
  const fillMissingFallbacks = Boolean(options.fillMissingFallbacks);

  Object.keys(IMAGES).forEach((k) => {
    delete IMAGES[k];
  });

  (rows || []).forEach((row) => {
    if (!row || !row.slug) return;
    const raw = String(row.url || "").trim();
    if (!raw) return;
    // Vídeos: manter URL direta (.mp4/.webm) — não converter via CDN de imagem do Drive
    if (
      String(row.slug).startsWith("video-") ||
      /\.(mp4|webm|ogg)(\?|#|$)/i.test(raw)
    ) {
      IMAGES[row.slug] = raw;
      return;
    }
    const src = driveToSrc(raw);
    if (src) {
      IMAGES[row.slug] = src;
    }
  });

  // Nunca repor slugs ausentes quando a API respondeu OK —
  // imagem desativada deve sumir do site (applyImages usa placeholder).
  if (fillMissingFallbacks) {
    Object.keys(IMAGE_FALLBACKS).forEach((key) => {
      if (!IMAGES[key]) IMAGES[key] = IMAGE_FALLBACKS[key];
    });
  }
}

function applyFallbackCatalog() {
  applyImageCatalog(
    Object.keys(IMAGE_FALLBACKS).map((slug) => ({
      slug,
      url: IMAGE_FALLBACKS[slug],
      title: slug,
    })),
    { fillMissingFallbacks: true },
  );
}
