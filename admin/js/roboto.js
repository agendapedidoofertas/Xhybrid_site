(function () {
  'use strict';

  var fab = document.getElementById('roboto-fab');
  var panel = document.getElementById('roboto-panel');
  var closeBtn = document.getElementById('roboto-close');
  var filterInput = document.getElementById('roboto-filter');
  var groupsEl = document.getElementById('roboto-groups');
  var threadEl = document.getElementById('roboto-thread');
  var dataEl = document.getElementById('roboto-data');
  var drawer = document.getElementById('roboto-drawer');
  var topicsOpenBtn = document.getElementById('roboto-topics-open');
  var drawerCloseBtn = document.getElementById('roboto-drawer-close');
  var headIcon = document.getElementById('roboto-head-icon');

  if (!fab || !panel || !groupsEl || !threadEl || !dataEl || !drawer) {
    return;
  }

  var payload;
  try {
    payload = JSON.parse(dataEl.textContent || '{}');
  } catch (e) {
    payload = { context: {}, chips: [] };
  }

  var chips = Array.isArray(payload.chips) ? payload.chips : [];
  var byId = {};
  chips.forEach(function (c) {
    if (c && c.id) {
      byId[c.id] = c;
    }
  });

  var CORE_ORDER = ['core_screen', 'core_plans', 'core_missing', 'core_not_found', 'core_agency'];
  var reduceMotion =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var busy = false;
  var replyTimer = null;
  var welcomed = false;

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function bodyToHtml(text) {
    return escapeHtml(text).replace(/\n/g, '<br>');
  }

  function formatStamp(date) {
    try {
      return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      }).format(date || new Date());
    } catch (e) {
      var d = date || new Date();
      return d.toLocaleString('pt-BR');
    }
  }

  function groupOrder(name) {
    var order = [
      'Principal',
      'Geral',
      'Contato',
      'Textos',
      'Imagens',
      'Marca',
      'Aparência',
      'Preset',
      'Visibilidade',
      'Serviços',
      'Senha',
      'Plano',
      'Equipe',
    ];
    var i = order.indexOf(name);
    return i === -1 ? 100 + name.charCodeAt(0) : i;
  }

  function sortedChips(list) {
    var core = [];
    var rest = [];
    list.forEach(function (c) {
      if (CORE_ORDER.indexOf(c.id) !== -1) {
        core.push(c);
      } else {
        rest.push(c);
      }
    });
    core.sort(function (a, b) {
      return CORE_ORDER.indexOf(a.id) - CORE_ORDER.indexOf(b.id);
    });
    rest.sort(function (a, b) {
      var ga = groupOrder(a.group || '');
      var gb = groupOrder(b.group || '');
      if (ga !== gb) {
        return ga - gb;
      }
      return String(a.title || '').localeCompare(String(b.title || ''), 'pt-BR');
    });
    return core.concat(rest);
  }

  function scrollThread() {
    threadEl.scrollTop = threadEl.scrollHeight;
  }

  function isDrawerOpen() {
    return !drawer.hidden;
  }

  function openDrawer() {
    drawer.hidden = false;
    panel.classList.add('is-drawer-open');
    if (topicsOpenBtn) {
      topicsOpenBtn.setAttribute('aria-expanded', 'true');
    }
    if (filterInput) {
      filterInput.focus();
    }
  }

  function closeDrawer() {
    drawer.hidden = true;
    panel.classList.remove('is-drawer-open');
    if (topicsOpenBtn) {
      topicsOpenBtn.setAttribute('aria-expanded', 'false');
    }
  }

  function appendRow(side) {
    var row = document.createElement('div');
    row.className = 'roboto-msg roboto-msg--' + side;
    threadEl.appendChild(row);
    return row;
  }

  function makeAvatar() {
    var avatar = document.createElement('div');
    avatar.className = 'roboto-msg__avatar';
    avatar.setAttribute('aria-hidden', 'true');
    if (headIcon) {
      avatar.innerHTML = headIcon.innerHTML;
    }
    return avatar;
  }

  function makeMeta() {
    var meta = document.createElement('div');
    meta.className = 'roboto-bubble__meta';
    meta.textContent = formatStamp(new Date());
    return meta;
  }

  function makeName(text) {
    var name = document.createElement('div');
    name.className = 'roboto-bubble__name';
    name.textContent = text;
    return name;
  }

  function buildCtas(ctas) {
    var wrap = document.createElement('div');
    wrap.className = 'roboto-bubble__ctas';
    (ctas || []).forEach(function (cta) {
      if (!cta || !cta.label) {
        return;
      }
      var href = cta.href || '';
      var a = document.createElement('a');
      a.textContent = cta.label;
      if (href.indexOf('#') === 0) {
        a.href = '#';
        a.addEventListener('click', function (ev) {
          ev.preventDefault();
          var id = href.slice(1);
          if (byId[id]) {
            selectChip(id);
          }
        });
      } else if (href) {
        a.href = href;
        if (href.indexOf('http') === 0) {
          a.target = '_blank';
          a.rel = 'noopener noreferrer';
        }
      } else {
        return;
      }
      wrap.appendChild(a);
    });
    return wrap.childNodes.length ? wrap : null;
  }

  function makeUserAvatar() {
    var avatar = document.createElement('div');
    avatar.className = 'roboto-msg__avatar roboto-msg__avatar--user';
    avatar.setAttribute('aria-hidden', 'true');
    avatar.innerHTML =
      '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">' +
      '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>' +
      '<circle cx="12" cy="7" r="4"/>' +
      '</svg>';
    return avatar;
  }

  function appendUserBubble(text) {
    var row = appendRow('user');
    var bubble = document.createElement('div');
    bubble.className = 'roboto-bubble roboto-bubble--user';
    bubble.appendChild(makeName('Usuário'));
    var body = document.createElement('div');
    body.className = 'roboto-bubble__body';
    body.textContent = text;
    bubble.appendChild(body);
    bubble.appendChild(makeMeta());
    row.appendChild(bubble);
    row.appendChild(makeUserAvatar());
    scrollThread();
  }

  function appendBotBubble(html, ctas) {
    var row = appendRow('bot');
    row.appendChild(makeAvatar());
    var bubble = document.createElement('div');
    bubble.className = 'roboto-bubble roboto-bubble--bot';
    bubble.appendChild(makeName('Roboto'));
    var body = document.createElement('div');
    body.className = 'roboto-bubble__body';
    body.innerHTML = html;
    bubble.appendChild(body);
    var ctaWrap = buildCtas(ctas);
    if (ctaWrap) {
      bubble.appendChild(ctaWrap);
    }
    bubble.appendChild(makeMeta());
    row.appendChild(bubble);
    scrollThread();
  }

  function appendTyping() {
    var row = appendRow('bot');
    row.classList.add('roboto-msg--typing');
    row.setAttribute('aria-label', 'Roboto digitando');
    row.appendChild(makeAvatar());
    var bubble = document.createElement('div');
    bubble.className = 'roboto-bubble roboto-bubble--bot roboto-bubble--typing';
    bubble.appendChild(makeName('Roboto'));
    var typing = document.createElement('div');
    typing.className = 'roboto-typing';
    typing.setAttribute('aria-hidden', 'true');
    typing.innerHTML = '<i></i><i></i><i></i>';
    bubble.appendChild(typing);
    row.appendChild(bubble);
    scrollThread();
    return row;
  }

  function ensureWelcome() {
    if (welcomed) {
      return;
    }
    welcomed = true;
    appendBotBubble(
      bodyToHtml(
        'Olá! Sou o Roboto.\nToque em “Escolher tópico” e eu respondo por aqui.'
      ),
      []
    );
  }

  function openPanel() {
    panel.hidden = false;
    fab.setAttribute('aria-expanded', 'true');
    ensureWelcome();
  }

  function closePanel() {
    closeDrawer();
    panel.hidden = true;
    fab.setAttribute('aria-expanded', 'false');
  }

  function setChipsBusy(isBusy) {
    groupsEl.querySelectorAll('.roboto-chip').forEach(function (btn) {
      btn.disabled = isBusy;
    });
    if (topicsOpenBtn) {
      topicsOpenBtn.disabled = isBusy;
    }
  }

  function showAnswer(chip) {
    if (!chip || busy) {
      return;
    }
    busy = true;
    closeDrawer();
    setChipsBusy(true);
    groupsEl.querySelectorAll('.roboto-chip').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-id') === chip.id);
    });

    appendUserBubble(chip.title || 'Pergunta');

    var delay = reduceMotion ? 0 : 750 + Math.floor(Math.random() * 400);
    var typingRow = delay > 0 ? appendTyping() : null;

    if (replyTimer) {
      clearTimeout(replyTimer);
    }

    replyTimer = setTimeout(function () {
      if (typingRow && typingRow.parentNode) {
        typingRow.parentNode.removeChild(typingRow);
      }
      appendBotBubble(bodyToHtml(chip.body || ''), chip.ctas || []);
      busy = false;
      setChipsBusy(false);
      replyTimer = null;
    }, delay);
  }

  function selectChip(id) {
    var chip = byId[id];
    if (!chip) {
      return;
    }
    showAnswer(chip);
  }

  function render(filter) {
    var q = String(filter || '')
      .trim()
      .toLowerCase();
    var filtered = chips.filter(function (c) {
      if (!q) {
        return true;
      }
      return String(c.title || '')
        .toLowerCase()
        .indexOf(q) !== -1;
    });
    filtered = sortedChips(filtered);

    var groups = {};
    filtered.forEach(function (c) {
      var g = c.group || 'Outros';
      if (!groups[g]) {
        groups[g] = [];
      }
      groups[g].push(c);
    });

    var names = Object.keys(groups).sort(function (a, b) {
      return groupOrder(a) - groupOrder(b);
    });

    groupsEl.innerHTML = '';
    if (!names.length) {
      groupsEl.innerHTML =
        '<p class="roboto-empty">Nenhum tópico com esse filtro.</p>';
      return;
    }

    names.forEach(function (name) {
      var section = document.createElement('section');
      section.className = 'roboto-group';
      var h = document.createElement('h3');
      h.className = 'roboto-group__title';
      h.textContent = name;
      section.appendChild(h);
      var wrap = document.createElement('div');
      wrap.className = 'roboto-chips';
      groups[name].forEach(function (c) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'roboto-chip';
        btn.setAttribute('data-id', c.id);
        btn.textContent = c.title;
        btn.disabled = busy;
        btn.addEventListener('click', function () {
          selectChip(c.id);
        });
        wrap.appendChild(btn);
      });
      section.appendChild(wrap);
      groupsEl.appendChild(section);
    });
  }

  fab.addEventListener('click', function () {
    if (panel.hidden) {
      openPanel();
    } else {
      closePanel();
    }
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closePanel);
  }

  if (topicsOpenBtn) {
    topicsOpenBtn.addEventListener('click', function () {
      if (busy) {
        return;
      }
      if (isDrawerOpen()) {
        closeDrawer();
      } else {
        openDrawer();
      }
    });
  }

  if (drawerCloseBtn) {
    drawerCloseBtn.addEventListener('click', closeDrawer);
  }

  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Escape' || panel.hidden) {
      return;
    }
    if (isDrawerOpen()) {
      closeDrawer();
      return;
    }
    closePanel();
  });

  if (filterInput) {
    filterInput.addEventListener('input', function () {
      render(filterInput.value);
    });
  }

  render('');
})();
