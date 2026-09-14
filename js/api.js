// Shared fetch helper. Both login.js and contacts.js use this one — don't write
// a second copy.
//
// credentials: 'same-origin' is what sends the PHP session cookie. Without it
// every request looks logged-out and you will lose an afternoon to it.
async function apiCall(endpoint, payload) {
  const res = await fetch('api/' + endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify(payload)
  });
  return res.json();
}
