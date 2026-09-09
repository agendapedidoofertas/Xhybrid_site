/**
 * Helpers de imagem (Google Drive) e mapa preenchido pela API.
 * As URLs vêm de /api/images.php (SQLite). IMAGE_FALLBACKS só se a API falhar.
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

function attachDriveFallback(img) {
  if (!img) return;
  img.addEventListener("error", function onErr() {
    img.removeEventListener("error", onErr);
    const id = driveFileId(img.currentSrc || img.src);
    if (id && !/thumbnail\?id=/.test(img.src)) {
      img.src = `https://drive.google.com/thumbnail?id=${id}&sz=w2000`;
    }
  });
}

/** Fallbacks locais — usados somente se /api/images.php falhar */
const IMAGE_FALLBACKS = {
  favicon: "favicon.svg",
  hero: "assets/hero.jpg",
  casal: "assets/casal.jpg",
  amigurumi: "assets/amigurumi.jpg",
  manta: "assets/manta.jpg",
  sousplat: "assets/sousplat.jpg",
  top: "assets/top.jpg",
  bolsa: "assets/bolsa.jpg",
  bebe: "assets/bebe.jpg",
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
  // imagem desativada deve sumir do site.
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
