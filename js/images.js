/**
 * Helpers de imagem (Google Drive) e mapa preenchido pela API.
 * As URLs vêm de /api/images.php (SQLite). IMAGE_FALLBACKS só se a API falhar.
 * Sem URL válida: frame vazio (is-media-empty) — sem robô placeholder.
 */

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
  return driveToSrc(url) || fallback || "";
}

function isPlaceholderSrc(src) {
  const s = String(src || "");
  return (
    /placeholder-robot\.svg(\?|$)/i.test(s) ||
    /^data:image\/svg\+xml/i.test(s)
  );
}

/** Frame visual em volta da mídia (hero, sobre, cards). */
function mediaFrameOf(img) {
  if (!img || !img.closest) return null;
  return (
    img.closest(
      ".hero__figure, .about-figure, .product-card__img-wrap, .gallery__item, .page-cover__media, .hero__media",
    ) || img.parentElement
  );
}

/**
 * Sem imagem: esconde o <img> e marca o frame com contorno (sem robô).
 * Com imagem: remove o estado vazio e mostra o <img>.
 */
function setMediaEmpty(img, empty) {
  if (!img) return;
  const frame = mediaFrameOf(img);
  if (empty) {
    img.removeAttribute("src");
    img.hidden = true;
    img.classList.remove("is-placeholder", "is-media-ready");
    img.classList.add("is-media-empty");
    if (frame) frame.classList.add("is-media-empty");
  } else {
    img.hidden = false;
    img.classList.remove("is-media-empty", "is-placeholder");
    if (frame) frame.classList.remove("is-media-empty");
  }
}

/**
 * Fallback em cascata: Drive thumbnail → frame vazio (sem robô).
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
    setMediaEmpty(img, true);
  });
}

function attachDriveFallback(img) {
  attachImageFallback(img);
}

/** Fallbacks locais — só marca (logo/favicon) se a API falhar.
 *  Hero/about/galeria sem URL: frame vazio (setMediaEmpty), não robô.
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
  // imagem desativada some do site (applyImages → frame vazio).
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
