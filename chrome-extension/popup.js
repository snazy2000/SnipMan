// ── State ──────────────────────────────────────────────────────────────────
let config = { baseUrl: '', token: '' };
let snippets = [];
let teams = [];
let currentSnippet = null;
let searchTimer = null;

// ── DOM refs ───────────────────────────────────────────────────────────────
const $ = (id) => document.getElementById(id);

// ── Init ───────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  config = await loadConfig();

  if (!config.baseUrl || !config.token) {
    show('not-configured');
    $('open-options').addEventListener('click', () => chrome.runtime.openOptionsPage());
    return;
  }

  show('main');
  await loadTeams();
  await loadSnippets();
  bindEvents();
});

// ── Config ─────────────────────────────────────────────────────────────────
function loadConfig() {
  return new Promise((resolve) => {
    chrome.storage.local.get(['baseUrl', 'token'], (data) => {
      resolve({ baseUrl: (data.baseUrl || '').replace(/\/$/, ''), token: data.token || '' });
    });
  });
}

// ── API ────────────────────────────────────────────────────────────────────
async function api(path) {
  const resp = await fetch(`${config.baseUrl}/api${path}`, {
    headers: {
      Authorization: `Bearer ${config.token}`,
      Accept: 'application/json',
    },
  });
  if (!resp.ok) throw new Error(`API error ${resp.status}`);
  return resp.json();
}

// ── Load data ──────────────────────────────────────────────────────────────
async function loadTeams() {
  try {
    const data = await api('/teams');
    teams = data.data || [];
    const sel = $('owner-filter');
    teams.forEach((t) => {
      const opt = document.createElement('option');
      opt.value = `team:${t.id}`;
      opt.textContent = t.name;
      sel.appendChild(opt);
    });
  } catch (_) {}
}

async function loadSnippets(q = '', owner = 'all') {
  show('list-loading');
  hide('snippet-list');
  hide('list-empty');

  try {
    const params = new URLSearchParams();
    if (q) params.set('q', q);
    if (owner !== 'all') params.set('owner', owner);
    const data = await api(`/snippets?${params}`);
    snippets = data.data || [];
    renderList();
  } catch (_) {
    snippets = [];
    renderList();
  }
}

// ── Render list ────────────────────────────────────────────────────────────
function renderList() {
  hide('list-loading');
  const list = $('snippet-list');
  list.innerHTML = '';

  if (!snippets.length) {
    show('list-empty');
    return;
  }

  show('snippet-list');
  snippets.forEach((s) => list.appendChild(makeSnippetRow(s)));
}

function makeSnippetRow(s) {
  const el = document.createElement('div');
  el.className = 'snippet-item';
  el.innerHTML = `
    <div class="snippet-info">
      <div class="snippet-title">${esc(s.title)}</div>
      <div class="snippet-meta">
        <span class="badge">${esc(s.language)}</span>
        ${s.team ? `<span class="badge badge-team">${esc(s.team.name)}</span>` : ''}
        ${s.folder ? `<span class="badge">${esc(s.folder.name)}</span>` : ''}
      </div>
      ${s.ai_description ? `<span class="snippet-desc">${esc(s.ai_description)}</span>` : ''}
    </div>
    <button class="snippet-copy" data-id="${s.id}">Copy</button>
  `;

  el.querySelector('.snippet-info').addEventListener('click', () => openDetail(s));
  el.querySelector('.snippet-copy').addEventListener('click', (e) => {
    e.stopPropagation();
    quickCopy(s, e.currentTarget);
  });

  return el;
}

async function quickCopy(s, btn) {
  try {
    let content = s.content;
    if (!content) {
      const full = await api(`/snippets/${s.id}`);
      content = full.content;
    }
    await navigator.clipboard.writeText(content);
    btn.textContent = 'Copied!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 1500);
  } catch (_) {
    btn.textContent = 'Error';
    setTimeout(() => { btn.textContent = 'Copy'; }, 1500);
  }
}

// ── Detail view ────────────────────────────────────────────────────────────
async function openDetail(s) {
  currentSnippet = s;
  hide('list-view');
  show('detail-view');

  $('detail-title').textContent = s.title;
  $('detail-lang').textContent = s.language;
  $('detail-description').textContent = s.ai_description || '';
  $('detail-tags').innerHTML = (s.user_tags || []).map((t) => `<span class="tag">${esc(t)}</span>`).join('');

  const codeEl = $('detail-code').querySelector('code');
  codeEl.textContent = 'Loading…';
  $('btn-copy').disabled = true;

  try {
    const full = await api(`/snippets/${s.id}`);
    currentSnippet = full;
    codeEl.textContent = full.content;
    $('btn-copy').disabled = false;
  } catch (_) {
    codeEl.textContent = 'Failed to load content.';
  }
}

// ── Events ─────────────────────────────────────────────────────────────────
function bindEvents() {
  $('btn-back').addEventListener('click', () => { hide('detail-view'); show('list-view'); });
  $('btn-settings').addEventListener('click', () => chrome.runtime.openOptionsPage());

  $('btn-new').addEventListener('click', () => {
    chrome.tabs.create({ url: `${config.baseUrl}/snippets/create` });
  });

  $('btn-copy').addEventListener('click', async () => {
    if (!currentSnippet?.content) return;
    await navigator.clipboard.writeText(currentSnippet.content);
    $('btn-copy').textContent = 'Copied!';
    setTimeout(() => ($('btn-copy').textContent = 'Copy'), 1500);
  });

  $('search').addEventListener('input', (e) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadSnippets(e.target.value, $('owner-filter').value), 300);
  });

  $('owner-filter').addEventListener('change', (e) => {
    loadSnippets($('search').value, e.target.value);
  });
}

// ── Helpers ────────────────────────────────────────────────────────────────
function show(id) { $(id).classList.remove('hidden'); }
function hide(id) { $(id).classList.add('hidden'); }
function esc(str) {
  return String(str ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
