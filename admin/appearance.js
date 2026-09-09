(function () {
  const root = document.getElementById("admin-appearance");
  if (!root || typeof LOOK_PRESETS === "undefined") return;

  const state = {
    look: root.dataset.selectedLook || DEFAULT_LOOK,
  };

  const fieldLook = document.getElementById("field-look");
  const demo = document.getElementById("appearance-demo");
  const demoToggle = document.getElementById("appearance-demo-toggle");
  const frame = document.getElementById("appearance-frame");
  const statusEl = document.getElementById("appearance-status");

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function currentPreset() {
    return LOOK_PRESETS.find((l) => l.id === state.look) || LOOK_PRESETS[0];
  }

  function syncFields() {
    if (fieldLook) fieldLook.value = state.look;
  }

  function pushPreview() {
    if (!frame || !frame.contentWindow) return;
    const preset = currentPreset();
    try {
      frame.contentWindow.postMessage(
        {
          type: "xhybrid-appearance-preview",
          look: preset.id,
          theme: preset.theme,
          font: preset.font,
          layout: preset.layout,
          media: preset.media,
        },
        window.location.origin
      );
    } catch (_) {
      /* ignore */
    }
  }

  function markDirty() {
    const preset = currentPreset();
    if (statusEl) {
      statusEl.textContent = `Look “${preset.nome}” na demo — salve para publicar.`;
    }
  }

  function renderLooks() {
    const mount = document.getElementById("opt-looks");
    if (!mount) return;

    const groups = [];
    LOOK_PRESETS.forEach((look) => {
      const name = look.palette || "Outros";
      let group = groups.find((g) => g.name === name);
      if (!group) {
        group = { name, looks: [] };
        groups.push(group);
      }
      group.looks.push(look);
    });

    mount.innerHTML = groups
      .map((group) => {
        const items = group.looks
          .map((look) => {
            const active = look.id === state.look;
            const swatches = look.swatch
              .map((c) => `<span style="background:${c}"></span>`)
              .join("");
            return `
        <button type="button" class="admin-appearance__look${active ? " is-active" : ""}" data-look-id="${escapeHtml(look.id)}" aria-pressed="${active}">
          <span class="admin-appearance__look-swatch">${swatches}</span>
          <span class="admin-appearance__look-copy">
            <span class="admin-appearance__look-name">${escapeHtml(look.nome)}</span>
            <span class="admin-appearance__look-desc">${escapeHtml(look.descricao)}</span>
          </span>
        </button>`;
          })
          .join("");
        return `
      <div class="admin-appearance__palette" role="group" aria-label="${escapeHtml(group.name)}">
        <h3 class="admin-appearance__palette-title">${escapeHtml(group.name)}</h3>
        <div class="admin-appearance__palette-list">${items}</div>
      </div>`;
      })
      .join("");
  }

  function selectLook(id) {
    if (!LOOK_PRESETS.some((l) => l.id === id)) return;
    state.look = id;
    renderLooks();
    syncFields();
    pushPreview();
    markDirty();
  }

  root.addEventListener("click", (e) => {
    const lookBtn = e.target.closest("[data-look-id]");
    if (!lookBtn) return;
    selectLook(lookBtn.getAttribute("data-look-id"));
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

  renderLooks();
  syncFields();
})();
