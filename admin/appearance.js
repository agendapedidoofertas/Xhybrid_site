(function () {
  const root = document.getElementById("admin-appearance");
  if (!root || typeof LOOK_PRESETS === "undefined") return;

  const state = {
    look: root.dataset.selectedLook || DEFAULT_LOOK,
  };

  const fieldLook = document.getElementById("field-look");
  const lookSelect = document.getElementById("appearance_look");
  const statusEl = document.getElementById("appearance-status");

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function syncFields() {
    if (fieldLook) fieldLook.value = state.look;
    if (lookSelect) {
      lookSelect.value = state.look;
      lookSelect.dispatchEvent(new Event("change"));
    }
  }

  function markDirty() {
    const source = typeof allowedLookPresets === "function" ? allowedLookPresets() : LOOK_PRESETS;
    const preset = source.find((l) => l.id === state.look) || source[0];
    if (statusEl && preset) {
      statusEl.textContent = `Look “${preset.nome}” selecionado — salve para publicar.`;
    }
  }

  function renderLooks() {
    const mount = document.getElementById("opt-looks");
    if (!mount) return;

    const source = typeof allowedLookPresets === "function" ? allowedLookPresets() : LOOK_PRESETS;
    const groups = [];
    source.forEach((look) => {
      const name = look.palette || "Outros";
      let group = groups.find((g) => g.name === name);
      if (!group) {
        group = { name, looks: [] };
        groups.push(group);
      }
      group.looks.push(look);
    });

    if (!source.some((l) => l.id === state.look) && source[0]) {
      state.look = source[0].id;
    }

    mount.innerHTML = groups
      .map((group) => {
        const items = group.looks
          .map((look) => {
            const active = look.id === state.look;
            const swatches = (look.swatch || [])
              .map((c) => `<span style="background:${c}"></span>`)
              .join("");
            return `
        <button type="button" class="admin-appearance__look${active ? " is-active" : ""}" data-look-id="${escapeHtml(look.id)}" aria-pressed="${active}" title="${escapeHtml(look.descricao || "")}">
          <span class="admin-appearance__look-swatch">${swatches}</span>
          <span class="admin-appearance__look-name">${escapeHtml(look.nome)}${look.agencyExclusive ? " · agência" : ""}</span>
          <span class="admin-appearance__look-desc">${escapeHtml(look.descricao || "")}</span>
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
    const source = typeof allowedLookPresets === "function" ? allowedLookPresets() : LOOK_PRESETS;
    const preset = source.find((l) => l.id === id);
    if (!preset) return;
    state.look = id;
    renderLooks();
    syncFields();
    markDirty();
    if (typeof window.__xhybridAppearanceSyncFromLook === "function") {
      window.__xhybridAppearanceSyncFromLook(preset);
    }
  }

  root.addEventListener("click", (e) => {
    const lookBtn = e.target.closest("[data-look-id]");
    if (!lookBtn) return;
    selectLook(lookBtn.getAttribute("data-look-id"));
  });

  renderLooks();
  syncFields();
})();
