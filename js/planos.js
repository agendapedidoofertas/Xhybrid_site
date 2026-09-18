(function () {
  const fallback = window.XH_PLANOS;
  if (!fallback) return;

  const state = { plan: '', provider: '', accepted: false, offer: null };
  const grid = document.getElementById('planos-grid');
  const hint = document.getElementById('planos-hint');
  const continueBtn = document.getElementById('planos-continue');
  const accept = document.getElementById('terms-accept');
  const modal = document.getElementById('legal-modal');
  const modalBody = document.getElementById('legal-modal-body');
  const modalTitle = document.getElementById('legal-modal-title');
  const fullBtn = document.getElementById('legal-modal-full');
  const introEl = document.getElementById('planos-intro');
  const eyebrowEl = document.getElementById('planos-eyebrow');
  const titleEl = document.getElementById('planos-title');
  const referralEl = document.getElementById('planos-referral');

  let legalKind = 'terms';
  let legalMode = 'summary';
  const fullCache = { terms: '', privacy: '' };

  const DOCS = {
    terms: {
      titleSummary: 'Termos de Serviço (resumo)',
      titleFull: 'Termos de Serviço',
      summaryKey: 'terms_summary',
      urlKey: 'terms_url',
      fallbackUrl: '/termos.html',
    },
    privacy: {
      titleSummary: 'Privacidade / LGPD (resumo)',
      titleFull: 'Política de Privacidade (LGPD)',
      summaryKey: 'privacy_summary',
      urlKey: 'privacy_url',
      fallbackUrl: '/privacidade.html',
    },
  };

  function offer() {
    return state.offer || fallback;
  }

  function money(cents) {
    return fallback.formatBRL(cents);
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function summaryToHtml(text) {
    const parts = String(text || '')
      .split(/\n\s*\n/)
      .map((p) => p.trim())
      .filter(Boolean);
    if (!parts.length) {
      return '<p>Documento indisponível no momento.</p>';
    }
    return parts
      .map((p) => '<p>' + escapeHtml(p).replace(/\n/g, '<br>') + '</p>')
      .join('');
  }

  function renderCards() {
    const cfg = offer();
    const plans = cfg.plans || {};
    const highlight = cfg.highlight || 'pleno';
    grid.innerHTML = '';

    Object.values(plans).forEach((p) => {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'plan-pick' + (p.id === highlight ? ' is-highlight' : '');
      btn.dataset.plan = p.id;
      btn.setAttribute('aria-pressed', 'false');

      const total = (p.planCents || 0) + (p.maintenanceCents || 0);
      const badge =
        p.id === highlight
          ? '<span class="plan-pick__badge">Mais escolhido</span>'
          : '';

      btn.innerHTML =
        badge +
        '<h2 class="font-display plan-pick__title">' +
        escapeHtml(p.label) +
        '</h2>' +
        '<p class="plan-pick__price">' +
        money(p.planCents) +
        ' <span>/mês</span></p>' +
        '<p class="plan-pick__maint">Manutenção: ' +
        money(p.maintenanceCents) +
        ' /mês</p>' +
        '<p class="plan-pick__total">Total aproximado: ' +
        money(total) +
        ' /mês</p>' +
        '<ul>' +
        (p.bullets || []).map((b) => '<li>' + escapeHtml(b) + '</li>').join('') +
        '</ul>' +
        '<span class="btn btn-primary" style="align-self:flex-start;margin-top:auto;pointer-events:none;">Escolher</span>';

      btn.addEventListener('click', () => {
        state.plan = p.id;
        hint.dataset.sticky = '0';
        refreshUi();
      });

      grid.appendChild(btn);
    });
  }

  function refreshUi() {
    document.querySelectorAll('.plan-pick').forEach((c) => {
      const on = c.dataset.plan === state.plan;
      c.classList.toggle('is-selected', on);
      c.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
    document.querySelectorAll('.pay-option').forEach((b) => {
      b.classList.toggle('is-selected', b.dataset.provider === state.provider);
    });
    continueBtn.disabled = !(state.plan && state.provider && state.accepted);
    if (!hint.textContent || hint.dataset.sticky !== '1') {
      hint.textContent = '';
    }
  }

  function showHint(msg) {
    hint.textContent = msg;
    hint.dataset.sticky = '1';
  }

  function applyMeta() {
    const cfg = offer();
    if (eyebrowEl && cfg.eyebrow) eyebrowEl.textContent = cfg.eyebrow;
    if (titleEl && cfg.title) titleEl.textContent = cfg.title;
    if (introEl && cfg.intro) introEl.textContent = cfg.intro;
    if (referralEl && cfg.referral_note) referralEl.textContent = cfg.referral_note;
  }

  function fillSummary() {
    const doc = DOCS[legalKind];
    legalMode = 'summary';
    if (modalTitle) modalTitle.textContent = doc.titleSummary;
    if (modalBody) modalBody.innerHTML = summaryToHtml(offer()[doc.summaryKey]);
    if (fullBtn) fullBtn.textContent = 'Ver texto completo';
  }

  async function fillFull() {
    const doc = DOCS[legalKind];
    const url = offer()[doc.urlKey] || doc.fallbackUrl;
    legalMode = 'full';
    if (modalTitle) modalTitle.textContent = doc.titleFull;
    if (fullBtn) fullBtn.textContent = 'Ver resumo';

    if (fullCache[legalKind]) {
      if (modalBody) modalBody.innerHTML = fullCache[legalKind];
      return;
    }
    if (modalBody) modalBody.innerHTML = '<p>Carregando…</p>';
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      const html = await res.text();
      const parsed = new DOMParser().parseFromString(html, 'text/html');
      const main = parsed.querySelector('main') || parsed.body;
      const clone = main.cloneNode(true);
      clone.querySelectorAll('script, style, link, nav, header, footer').forEach((n) => n.remove());
      fullCache[legalKind] = clone.innerHTML;
      if (modalBody) modalBody.innerHTML = fullCache[legalKind];
    } catch (e) {
      if (modalBody) {
        modalBody.innerHTML =
          summaryToHtml(offer()[doc.summaryKey]) +
          '<p style="margin-top:1rem;">Não foi possível carregar o texto completo. Use o resumo acima ou tente de novo.</p>';
      }
    }
  }

  function openLegal(kind) {
    legalKind = kind === 'privacy' ? 'privacy' : 'terms';
    fillSummary();
    modal.hidden = false;
    document.body.style.overflow = 'hidden';
  }

  function closeLegal() {
    modal.hidden = true;
    document.body.style.overflow = '';
  }

  function mountRoboto(payload) {
    const dataEl = document.getElementById('roboto-data');
    const fab = document.getElementById('roboto-fab');
    if (!dataEl || !payload || !Array.isArray(payload.chips) || !payload.chips.length) {
      return;
    }
    dataEl.textContent = JSON.stringify(payload);
    if (fab) fab.hidden = false;
    if (window.__planosRobotoLoaded) return;
    window.__planosRobotoLoaded = true;
    const s = document.createElement('script');
    s.src = '/admin/js/roboto.js';
    s.defer = true;
    document.body.appendChild(s);
  }

  document.querySelectorAll('.pay-option').forEach((b) => {
    b.addEventListener('click', () => {
      state.provider = b.dataset.provider || '';
      hint.dataset.sticky = '0';
      refreshUi();
    });
  });

  accept.addEventListener('change', () => {
    state.accepted = !!accept.checked;
    hint.dataset.sticky = '0';
    refreshUi();
  });

  continueBtn.addEventListener('click', () => {
    if (!state.plan) {
      showHint('Escolha um plano.');
      return;
    }
    if (!state.provider) {
      showHint('Escolha Asaas ou Stripe.');
      return;
    }
    if (!state.accepted) {
      showHint('Marque que concorda com os Termos e a Privacidade.');
      return;
    }
    const q = new URLSearchParams({
      plan: state.plan,
      provider: state.provider,
      terms: '1',
    });
    location.href = '/checkout.html?' + q.toString();
  });

  const openTerms = document.getElementById('open-termos-modal');
  const openPrivacy = document.getElementById('open-privacy-modal');
  if (openTerms) {
    openTerms.addEventListener('click', (e) => {
      e.preventDefault();
      openLegal('terms');
    });
  }
  if (openPrivacy) {
    openPrivacy.addEventListener('click', (e) => {
      e.preventDefault();
      openLegal('privacy');
    });
  }
  document.getElementById('legal-modal-close').addEventListener('click', closeLegal);
  fullBtn.addEventListener('click', () => {
    if (legalMode === 'full') {
      fillSummary();
    } else {
      fillFull();
    }
  });
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeLegal();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.hidden) closeLegal();
  });

  async function boot() {
    try {
      const res = await fetch('/api/planos_offer.php', { credentials: 'same-origin' });
      if (res.ok) {
        const data = await res.json();
        if (data && data.plans) {
          state.offer = {
            intro: data.intro || fallback.intro,
            eyebrow: data.eyebrow || fallback.eyebrow,
            title: data.title || fallback.title,
            highlight: data.highlight || fallback.highlight,
            referral_note: data.referral_note || fallback.referral_note,
            terms_summary: data.terms_summary || fallback.terms_summary,
            terms_url: data.terms_url || fallback.terms_url,
            privacy_summary: data.privacy_summary || fallback.privacy_summary,
            privacy_url: data.privacy_url || fallback.privacy_url,
            plans: data.plans,
          };
          if (data.roboto) mountRoboto(data.roboto);
        }
      }
    } catch (e) {
      /* fallback estático */
    }
    applyMeta();
    renderCards();
    refreshUi();
  }

  boot();
})();
