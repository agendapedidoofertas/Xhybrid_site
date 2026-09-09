(function () {
  const root = document.getElementById("admin-appearance");
  if (!root || typeof THEMES === "undefined") return;

  const state = {
    theme: root.dataset.theme || DEFAULT_THEME,
    font: root.dataset.font || DEFAULT_FONT,
    layout: root.dataset.layout || DEFAULT_LAYOUT,
    media: root.dataset.media || DEFAULT_MEDIA,
  };

  const fieldTheme = document.getElementById("field-theme");
  const fieldFont = document.getElementById("field-font");
  const fieldLayout = document.getElementById("field-layout");
  const fieldMedia = document.getElementById("field-media");
  const demo = document.getElementById("appearance-demo");
  const demoToggle = document.getElementById("appearance-demo-toggle");
  const remixBtn = document.getElementById("appearance-remix");
  const frame = document.getElementById("appearance-frame");
  const statusEl = document.getElementById("appearance-status");

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function syncFields() {
    if (fieldTheme) fieldTheme.value = state.theme;
    if (fieldFont) fieldFont.value = state.font;
    if (fieldLayout) fieldLayout.value = state.layout;
    if (fieldMedia) fieldMedia.value = state.media;
  }

  function pushPreview() {
    if (!frame || !frame.contentWindow) return;
    try {
      frame.contentWindow.postMessage(
        {
          type: "xhybrid-appearance-preview",
          theme: state.theme,
          font: state.font,
          layout: state.layout,
          media: state.media,
        },
        window.location.origin
      );
    } catch (_) {
      /* ignore */
    }
  }

  function markDirty() {
    if (statusEl) {
      statusEl.textContent = "Pré-visualização atualizada — salve para publicar no site.";
    }
  }

  function renderThemes() {
    const mount = document.getElementById("opt-themes");
    if (!mount) return;
    mount.innerHTML = THEMES.map((t) => {
      const active = t.id === state.theme;
      const swatches = t.swatch
        .map((c) => `<span style="background:${c}"></span>`)
        .join("");
      return `
        <button type="button" class="admin-appearance__swatch${active ? " is-active" : ""}" data-theme-id="${escapeHtml(t.id)}" aria-pressed="${active}" title="${escapeHtml(t.nome)}">
          <span class="admin-appearance__swatch-colors">${swatches}</span>
          <span class="admin-appearance__swatch-name">${escapeHtml(t.nome)}</span>
        </button>`;
    }).join("");
  }

  function renderList(mountId, items, currentId, dataAttr) {
    const mount = document.getElementById(mountId);
    if (!mount) return;
    mount.innerHTML = items
      .map((item) => {
        const active = item.id === currentId;
        return `
          <button type="button" class="admin-appearance__choice${active ? " is-active" : ""}" ${dataAttr}="${escapeHtml(item.id)}" aria-pressed="${active}">
            <span class="admin-appearance__choice-name">${escapeHtml(item.nome)}</span>
            <span class="admin-appearance__choice-desc">${escapeHtml(item.descricao)}</span>
          </button>`;
      })
      .join("");
  }

  function renderAll() {
    renderThemes();
    renderList("opt-fonts", FONT_PACKS, state.font, "data-font-id");
    renderList("opt-layouts", LAYOUT_PRESETS, state.layout, "data-layout-id");
    renderList("opt-media", MEDIA_PRESETS, state.media, "data-media-id");
    syncFields();
  }

  function pickRandom(list, avoidId) {
    if (!list.length) return null;
    if (list.length === 1) return list[0];
    let pick = list[Math.floor(Math.random() * list.length)];
    let guard = 0;
    while (avoidId && pick.id === avoidId && guard < 8) {
      pick = list[Math.floor(Math.random() * list.length)];
      guard += 1;
    }
    return pick;
  }

  function remix() {
    state.theme = (pickRandom(THEMES, state.theme) || THEMES[0]).id;
    state.font = (pickRandom(FONT_PACKS, state.font) || FONT_PACKS[0]).id;
    state.layout = (pickRandom(LAYOUT_PRESETS, state.layout) || LAYOUT_PRESETS[0]).id;
    state.media = (pickRandom(MEDIA_PRESETS, state.media) || MEDIA_PRESETS[0]).id;
    renderAll();
    pushPreview();
    markDirty();
  }

  root.addEventListener("click", (e) => {
    const themeBtn = e.target.closest("[data-theme-id]");
    if (themeBtn) {
      state.theme = themeBtn.getAttribute("data-theme-id");
      renderAll();
      pushPreview();
      markDirty();
      return;
    }
    const fontBtn = e.target.closest("[data-font-id]");
    if (fontBtn) {
      state.font = fontBtn.getAttribute("data-font-id");
      renderAll();
      pushPreview();
      markDirty();
      return;
    }
    const layoutBtn = e.target.closest("[data-layout-id]");
    if (layoutBtn) {
      state.layout = layoutBtn.getAttribute("data-layout-id");
      renderAll();
      pushPreview();
      markDirty();
      return;
    }
    const mediaBtn = e.target.closest("[data-media-id]");
    if (mediaBtn) {
      state.media = mediaBtn.getAttribute("data-media-id");
      renderAll();
      pushPreview();
      markDirty();
    }
  });

  if (demoToggle && demo) {
    demoToggle.addEventListener("click", () => {
      const open = demo.hasAttribute("hidden");
      if (open) {
        demo.removeAttribute("hidden");
        root.classList.add("is-demo-open");
        demoToggle.setAttribute("aria-pressed", "true");
        demoToggle.textContent = "Fechar demonstração";
        pushPreview();
      } else {
        demo.setAttribute("hidden", "");
        root.classList.remove("is-demo-open");
        demoToggle.setAttribute("aria-pressed", "false");
        demoToggle.textContent = "Demonstração";
      }
    });
  }

  if (remixBtn) remixBtn.addEventListener("click", remix);

  document.querySelectorAll("[data-preview-page]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const page = btn.getAttribute("data-preview-page");
      if (!page || !frame) return;
      document.querySelectorAll("[data-preview-page]").forEach((b) => b.classList.remove("is-active"));
      btn.classList.add("is-active");
      const sep = page.includes("?") ? "&" : "?";
      frame.src = page + sep + "preview=1";
    });
  });

  if (frame) {
    frame.addEventListener("load", () => {
      pushPreview();
    });
  }

  window.addEventListener("message", (event) => {
    if (event.origin !== window.location.origin) return;
    if (!event.data || event.data.type !== "xhybrid-appearance-ready") return;
    pushPreview();
  });

  renderAll();
})();
