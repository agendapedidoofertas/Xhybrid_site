/**
 * Biblioteca visual de ícones (admin).
 * Usa FEATURE_ICONS de ../js/feat-icons.js e FEAT_ICON_META injetado pela página.
 */
(function () {
  if (typeof FEATURE_ICONS === "undefined") return;

  const meta = window.FEAT_ICON_META || {};
  const labels = meta.labels || {};
  const categories = meta.categories || {};

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  function iconSvg(id) {
    return FEATURE_ICONS[id] || FEATURE_ICONS.layout || "";
  }

  function labelOf(id) {
    return labels[id] || id;
  }

  function shortLabel(id) {
    const full = labelOf(id);
    const part = full.split("/")[0].trim();
    return part || full;
  }

  function matchesQuery(id, query) {
    if (!query) return true;
    const q = query.toLowerCase();
    const hay = (id + " " + labelOf(id)).toLowerCase();
    return hay.includes(q);
  }

  function iconsForCategory(catId) {
    if (!catId || catId === "all") {
      return Object.keys(FEATURE_ICONS);
    }
    const cat = categories[catId];
    if (!cat || !Array.isArray(cat.icons)) return Object.keys(FEATURE_ICONS);
    return cat.icons.filter((id) => FEATURE_ICONS[id]);
  }

  function mountPicker(root) {
    const fieldId = root.getAttribute("data-for");
    const input = fieldId ? document.getElementById(fieldId) : null;
    if (!input) return;

    let selected = input.value || root.getAttribute("data-value") || "layout";
    let category = "all";
    let query = "";

    const preview = root.querySelector("[data-icon-preview]");
    const previewLabel = root.querySelector("[data-icon-preview-label]");
    const search = root.querySelector("[data-icon-search]");
    const chips = root.querySelector("[data-icon-chips]");
    const grid = root.querySelector("[data-icon-grid]");

    function syncPreview() {
      if (preview) preview.innerHTML = iconSvg(selected);
      if (previewLabel) previewLabel.textContent = labelOf(selected);
      input.value = selected;
    }

    function renderChips() {
      if (!chips) return;
      const items = [{ id: "all", label: "Todos" }].concat(
        Object.keys(categories).map((id) => ({
          id,
          label: categories[id].label || id,
        }))
      );
      chips.innerHTML = items
        .map(
          (item) =>
            `<button type="button" class="icon-lib__chip${
              category === item.id ? " is-active" : ""
            }" data-cat="${escapeHtml(item.id)}">${escapeHtml(item.label)}</button>`
        )
        .join("");
    }

    function renderGrid() {
      if (!grid) return;
      const ids = iconsForCategory(category).filter((id) => matchesQuery(id, query));
      if (!ids.length) {
        grid.innerHTML = `<p class="icon-lib__empty">Nenhum ícone encontrado.</p>`;
        return;
      }
      grid.innerHTML = ids
        .map((id) => {
          const active = id === selected ? " is-active" : "";
          return `<button type="button" class="icon-lib__opt${active}" data-icon="${escapeHtml(
            id
          )}" title="${escapeHtml(labelOf(id))}" aria-pressed="${
            id === selected ? "true" : "false"
          }"><span class="icon-lib__glyph" aria-hidden="true">${iconSvg(
            id
          )}</span><span class="icon-lib__name">${escapeHtml(shortLabel(id))}</span></button>`;
        })
        .join("");
    }

    function refresh() {
      renderChips();
      renderGrid();
      syncPreview();
    }

    if (chips) {
      chips.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-cat]");
        if (!btn) return;
        category = btn.getAttribute("data-cat") || "all";
        renderChips();
        renderGrid();
      });
    }

    if (grid) {
      grid.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-icon]");
        if (!btn) return;
        selected = btn.getAttribute("data-icon") || selected;
        renderGrid();
        syncPreview();
      });
    }

    if (search) {
      search.addEventListener("input", () => {
        query = search.value || "";
        renderGrid();
      });
    }

    refresh();
  }

  document.querySelectorAll("[data-icon-library]").forEach(mountPicker);
})();
