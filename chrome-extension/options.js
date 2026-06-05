const urlInput = document.getElementById('base-url');
const tokenInput = document.getElementById('token');
const saveBtn = document.getElementById('save');
const statusEl = document.getElementById('status');

// Load saved config
chrome.storage.local.get(['baseUrl', 'token'], ({ baseUrl, token }) => {
  if (baseUrl) urlInput.value = baseUrl;
  if (token) tokenInput.value = token;
});

saveBtn.addEventListener('click', async () => {
  const baseUrl = urlInput.value.trim().replace(/\/$/, '');
  const token = tokenInput.value.trim();

  if (!baseUrl || !token) {
    setStatus('Both fields are required.', false);
    return;
  }

  saveBtn.disabled = true;
  saveBtn.textContent = 'Testing…';
  setStatus('', null);

  try {
    const resp = await fetch(`${baseUrl}/api/snippets`, {
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json' },
    });

    if (resp.status === 401) {
      setStatus('Invalid token — check it and try again.', false);
      return;
    }
    if (!resp.ok) {
      setStatus(`Connection error (${resp.status}).`, false);
      return;
    }

    chrome.storage.local.set({ baseUrl, token }, () => {
      setStatus('Saved! Extension is ready.', true);
    });
  } catch (e) {
    setStatus('Could not reach that URL. Check it and try again.', false);
  } finally {
    saveBtn.disabled = false;
    saveBtn.textContent = 'Save & Test';
  }
});

function setStatus(msg, ok) {
  statusEl.textContent = msg;
  statusEl.className = 'status' + (ok === true ? ' ok' : ok === false ? ' err' : '');
}
