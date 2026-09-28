// Wires the login and register forms to api/login.php and api/register.php.
// Uses apiCall() from api.js, which is loaded above this script in index.html.

function wireAuthForm(formId, errorId, endpoint) {
  const form = document.getElementById(formId);
  const errorEl = document.getElementById(errorId);

  form.addEventListener('submit', async (event) => {
    // Stop the browser's default GET submit, which would put the password in the URL.
    event.preventDefault();
    errorEl.textContent = '';

    const payload = Object.fromEntries(new FormData(form));
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;

    try {
      const result = await apiCall(endpoint, payload);
      if (result.error) {
        errorEl.textContent = result.error;
        return;
      }
      window.location.href = 'contacts.html';
    } catch (err) {
      errorEl.textContent = 'Could not reach the server. Please try again.';
    } finally {
      button.disabled = false;
    }
  });
}

wireAuthForm('login-form', 'login-error', 'login.php');
wireAuthForm('register-form', 'register-error', 'register.php');
