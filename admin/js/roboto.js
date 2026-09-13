(function () {
  'use strict';

  var fab = document.getElementById('roboto-fab');
  var panel = document.getElementById('roboto-panel');
  var closeBtn = document.getElementById('roboto-close');
  var filterInput = document.getElementById('roboto-filter');
  var groupsEl = document.getElementById('roboto-groups');
  var answerEl = document.getElementById('roboto-answer');
  var answerTitle = document.getElementById('roboto-answer-title');
  var answerBody = document.getElementById('roboto-answer-body');
  var answerCtas = document.getElementById('roboto-answer-ctas');
  var dataEl = document.getElementById('roboto-data');

  if (!fab || !panel || !groupsEl || !dataEl) {
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

  function openPanel() {
    panel.hidden = false;
    fab.setAttribute('aria-expanded', 'true');
    if (filterInput) {
      filterInput.focus();
    }
  }

  function closePanel() {
    panel.hidden = true;
    fab.setAttribute('aria-expanded', 'false');
  }

  function showAnswer(chip) {
    if (!chip) {
      return;
    }
    answerEl.hidden = false;
    answerTitle.textContent = chip.title || '';
    answerBody.innerHTML = bodyToHtml(chip.body || '');
    answerCtas.innerHTML = '';
    (chip.ctas || []).forEach(function (cta) {
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
      answerCtas.appendChild(a);
    });
    answerEl.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
  }

  function selectChip(id) {
    var chip = byId[id];
    if (!chip) {
      return;
    }
    groupsEl.querySelectorAll('.roboto-chip').forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-id') === id);
    });
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
      groupsEl.innerHTML = '<p class="text-muted" style="margin:0.5rem 0;">Nenhum tópico com esse filtro.</p>';
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

  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape' && !panel.hidden) {
      closePanel();
    }
  });

  if (filterInput) {
    filterInput.addEventListener('input', function () {
      render(filterInput.value);
    });
  }

  render('');
})();
