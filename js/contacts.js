// Main front-end logic: load, add, edit, delete, search.
// Uses apiCall() from api.js — add <script src="js/api.js"></script> above this
// tag in contacts.html.
//
// TODO: load contacts on page load (empty search returns everything)
// TODO: add / edit / delete handlers
// TODO: search — must hit the server on every query. Filtering a cached array
//       in JS is explicitly forbidden and costs 5 points.
// TODO: debounce the search input ~300ms so you don't fire one request per
//       keystroke. Worth mentioning in the presentation.
// TODO: redirect to index.html if any call returns a "Not logged in" error.
