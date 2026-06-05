// Listens for a message from the popup to get selected text on the page.
chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
  if (message.type === 'GET_SELECTION') {
    sendResponse({ text: window.getSelection().toString() });
  }
});
