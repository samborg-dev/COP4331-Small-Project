// Main front-end logic for contacts.html: load, add, edit, delete, search.
// Uses apiCall() from api.js, which is loaded above this script in contacts.html.

// Grab the parts of the page we need to change.
const listEl = document.getElementById('contacts-list');
const countEl = document.getElementById('contact-count');
const errorEl = document.getElementById('contacts-error');
const searchBox = document.getElementById('contact-search');

// Calls the API and handles every failure in one place.
// Returns the server's answer if it worked, or null if it failed
// (the error message is already shown on the page).
async function callApi(endpoint, payload) {
  let result;
  try {
    result = await apiCall(endpoint, payload);
  } catch (err) {
    errorEl.textContent = 'Could not reach the server. Please try again.';
    return null;
  }

  // The session expired or the user never logged in: send them to the login page.
  if (result.error === 'Not logged in') {
    window.location.href = 'index.html';
    return null;
  }

  // Any other error text from the server: show it.
  if (result.error) {
    errorEl.textContent = result.error;
    return null;
  }

  // It worked: clear any old error message.
  errorEl.textContent = '';
  return result;
}

// Makes one table cell. textContent (not innerHTML) keeps a contact's name
// as plain text, so a name containing HTML or script can't run.
function makeCell(text) {
  const cell = document.createElement('td');
  cell.textContent = text || '';
  return cell;
}

// Makes one small text button for a table row (Edit or Delete).
// aria-label tells screen readers which contact the button is for,
// e.g. "Delete John Smith" instead of just "Delete".
function makeButton(label, contact) {
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'text-button';
  button.textContent = label;
  button.setAttribute('aria-label', label + ' ' + contact.firstName + ' ' + contact.lastName);
  return button;
}

// Draws the table from an array of contacts sent by the server.
function renderContacts(contacts) {
  listEl.replaceChildren();

  if (contacts.length === 0) {
    const row = document.createElement('tr');
    row.className = 'empty-row';
    const cell = makeCell('No contacts to display.');
    cell.colSpan = 5;
    row.append(cell);
    listEl.append(row);
  }
  for (const contact of contacts) {
    const row = document.createElement('tr');

    const editButton = makeButton('Edit', contact);
    editButton.addEventListener('click', () => openContactForm(contact));

    const deleteButton = makeButton('Delete', contact);
    deleteButton.classList.add('text-button-danger');
    deleteButton.addEventListener('click', () => deleteContact(contact));

    const actionsCell = document.createElement('td');
    actionsCell.className = 'row-actions';
    actionsCell.append(editButton, deleteButton);

    row.append(
      makeCell(contact.firstName),
      makeCell(contact.lastName),
      makeCell(contact.phone),
      makeCell(contact.email),
      actionsCell
    );
    listEl.append(row);
  }
  countEl.textContent = contacts.length + (contacts.length === 1 ? ' contact' : ' contacts');
}

// Counts searches so a slow, older answer from the server can't
// replace the results of a newer search that already came back.
let latestSearch = 0;

// Asks the server for contacts matching what's in the search box.
// An empty search box returns all contacts.
async function loadContacts() {
  latestSearch = latestSearch + 1;
  const thisSearch = latestSearch;

  const result = await callApi('searchContacts.php', { search: searchBox.value.trim() });

  if (result && thisSearch === latestSearch) {
    renderContacts(result.results);
  }
}

// Search hits the server every time (filtering in JavaScript is not allowed).
// Debounce: wait until the user stops typing for 300ms, then send ONE request,
// instead of one request per key press.
let searchTimer;
searchBox.addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadContacts, 300);
});

// Pressing Enter or clicking the Search button searches right away.
document.getElementById('search-form').addEventListener('submit', (event) => {
  event.preventDefault();
  clearTimeout(searchTimer);
  loadContacts();
});

// The add/edit form and its heading ("Add Contact" or "Edit Contact").
const contactForm = document.getElementById('contact-form');
const formHeading = document.getElementById('contact-form-heading');

// Shows the form. Pass nothing (null) for Add, or a contact object for Edit
// to fill the form with that contact's current details.
function openContactForm(contact) {
  contactForm.reset();

  if (contact) {
    contactForm.elements.id.value = contact.id;
    contactForm.elements.firstName.value = contact.firstName || '';
    contactForm.elements.lastName.value = contact.lastName || '';
    contactForm.elements.phone.value = contact.phone || '';
    contactForm.elements.email.value = contact.email || '';
    formHeading.textContent = 'Edit Contact';
  } else {
    contactForm.elements.id.value = '';
    formHeading.textContent = 'Add Contact';
  }

  contactForm.hidden = false;
  contactForm.elements.firstName.focus();
}

// Hides and clears the form.
function closeContactForm() {
  contactForm.reset();
  contactForm.hidden = true;
  formHeading.textContent = 'Add Contact';
}

document.getElementById('add-contact-button').addEventListener('click', () => {
  openContactForm(null);
});

document.getElementById('cancel-contact-button').addEventListener('click', closeContactForm);

// Save button: send the form to the server.
// If the hidden id field is empty it's a new contact (Add),
// otherwise it's an existing contact (Edit).
contactForm.addEventListener('submit', async (event) => {
  event.preventDefault();

  const idValue = contactForm.elements.id.value;
  const payload = {
    firstName: contactForm.elements.firstName.value.trim(),
    lastName: contactForm.elements.lastName.value.trim(),
    phone: contactForm.elements.phone.value.trim(),
    email: contactForm.elements.email.value.trim()
  };

  let endpoint = 'addContact.php';
  if (idValue !== '') {
    endpoint = 'editContact.php';
    payload.id = Number(idValue);
  }

  // Disable Save while waiting so a double-click can't create two contacts.
  const saveButton = contactForm.querySelector('button[type="submit"]');
  saveButton.disabled = true;
  const result = await callApi(endpoint, payload);
  saveButton.disabled = false;

  if (result) {
    closeContactForm();
    loadContacts();
  }
});

// Asks "are you sure?", then deletes the contact and reloads the list.
async function deleteContact(contact) {
  const sure = confirm('Delete ' + contact.firstName + ' ' + contact.lastName + '?');
  if (!sure) {
    return;
  }

  const result = await callApi('deleteContact.php', { id: Number(contact.id) });
  if (result) {
    loadContacts();
  }
}

// Run once when the page opens.
//loadContacts();
 renderContacts([{ id: 1, firstName: 'Test', lastName: 'Person', phone: '123', email: 'a@b.com' }]);