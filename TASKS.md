# Contact Manager — Team Task Breakdown

**Stack:** Linux + Apache + MySQL + PHP (LAMP) on a DigitalOcean droplet, with a
plain HTML/CSS/JavaScript front end that talks to the PHP API using `fetch()` and JSON.

No frameworks. No React, no Laravel, no Python. Just files.

---

## 0. Read this first (everyone)

### The single biggest grading item is your GitHub activity — 25 of 100 points.

That is more than the working demo (20). It is graded on **commit frequency,
consistency, and code contributions** — not on how much code exists at the end.

**Rules for everyone:**
- Commit **small and often**. 15 commits of 20 lines beats 1 commit of 300 lines.
- Commit **every 2–3 days minimum**, for all 18 days. A wall of commits the
  night before is visible in the graph and looks bad.
- Never let one person paste in everyone's work. Each person pushes their own files.
- Write real commit messages: `add search endpoint with partial match`, not `update`.
- Review at least two teammates' Pull Requests per phase and leave written comments.
  "Code reviews" is explicitly in the rubric.

### How we work in Git

Never commit straight to `main`. Every task:

```bash
git checkout main
git pull                          # get everyone else's latest work
git checkout -b yourname/what-youre-doing
# ...do the work...
git add .
git commit -m "clear description of what changed"
git push -u origin yourname/what-youre-doing
```

Then open a Pull Request on GitHub, tag a teammate, they comment, then merge.

If `git pull` yells about conflicts, **stop and ask in Discord** rather than
force-pushing. Force-push destroys other people's commits.

---

## 1. The architecture (so everyone knows where their file lives)

```
/var/www/html/                  <- this is what the web server serves
├── index.html                  Login + Register page (the landing page)
├── contacts.html               The contact manager (after you log in)
├── css/
│   └── style.css
├── js/
│   ├── login.js                talks to register.php / login.php
│   └── contacts.js             talks to the 4 contact endpoints
└── api/
    ├── db.php                  database connection (shared, nobody duplicates this)
    ├── helpers.php             JSON in/out + "is this user logged in?" check
    ├── register.php            POST  create account
    ├── login.php               POST  log in
    ├── logout.php              POST  log out
    ├── addContact.php          POST  create a contact
    ├── editContact.php         POST  update a contact
    ├── deleteContact.php       POST  delete a contact
    └── searchContacts.php      POST  server-side partial-match search
```

**The contract between front end and back end.** Everything is JSON. Every endpoint
takes a JSON body and returns JSON. Every response has an `error` field — empty
string `""` means success, any text means it failed.

```
POST /api/login.php
send:     { "login": "sam", "password": "hunter2" }
get back: { "id": 4, "firstName": "Sam", "lastName": "Borges", "error": "" }
       or { "id": 0, "firstName": "", "lastName": "", "error": "Invalid credentials" }
```

```
POST /api/searchContacts.php
send:     { "search": "smi" }
get back: { "results": [ {"id":1,"firstName":"John","lastName":"Smith",
                          "phone":"407-555-0100","email":"j@x.com"} ], "error": "" }
```

Front-end people can build against this contract **before** the API exists, using
fake data. Back-end people can build against it before the UI exists, using Postman.
Nobody waits.

### How we know who is logged in

We use **PHP sessions**. `login.php` sets `$_SESSION['userId']`. Every contact
endpoint starts by checking that it exists; if not, it returns an error. The browser
handles the session cookie automatically.

The one thing the front end must remember: every `fetch()` call needs
`credentials: 'same-origin'` or the cookie won't be sent and you'll get logged out
on every request. This will cost someone two hours if it isn't written down, so
it is written down.

**Do not send the user's ID from the browser.** If the browser says "give me user
5's contacts" and the server believes it, anyone can read anyone's contacts. The
server decides who you are, from the session. This is worth real points under
"adherence to current standards."

---

## 2. The database

Two tables. That's the whole thing.

```sql
CREATE TABLE Users (
    UserID       INT AUTO_INCREMENT PRIMARY KEY,
    FirstName    VARCHAR(50)  NOT NULL,
    LastName     VARCHAR(50)  NOT NULL,
    Login        VARCHAR(50)  NOT NULL UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL,
    CreatedAt    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Contacts (
    ContactID INT AUTO_INCREMENT PRIMARY KEY,
    UserID    INT NOT NULL,
    FirstName VARCHAR(50)  NOT NULL,
    LastName  VARCHAR(50)  NOT NULL,
    Phone     VARCHAR(25),
    Email     VARCHAR(100),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID) ON DELETE CASCADE,
    INDEX idx_user (UserID)
);
```

`Contacts.UserID` pointing at `Users.UserID` is the "no shared contacts" requirement.
Every single query on Contacts must include `WHERE UserID = ?`. Every one. No exceptions.

**Passwords are never stored as text.** PHP does this for you:
- Saving: `$hash = password_hash($plaintext, PASSWORD_DEFAULT);`
- Checking: `if (password_verify($plaintext, $row['PasswordHash'])) { ... }`

If someone suggests storing passwords plainly or with MD5, say no.

---

## 3. Assignments

Each person owns their files. If you need to change someone else's file, message
them first — that's how merge conflicts get avoided.

---

### Noah — Database + API

You own the data. Nobody else writes schema.

**Phase 1 — Sep 9–13**
- [ ] Create the MySQL database on the droplet (Sam gives you server access).
- [ ] Create a dedicated MySQL user for the app — **not root**. Grant it access to
      only this one database.
- [ ] Write `sql/schema.sql` with the two `CREATE TABLE` statements above and commit
      it. This file lets anyone rebuild the DB from scratch.
- [ ] Write `sql/seed.sql` — 2 test users and ~15 fake contacts. The team needs data
      to test search against, and 15 rows makes partial matching actually visible.
- [ ] Write `api/db.php`: opens a PDO connection and stops the script with a JSON
      error if the connection fails.

`db.php` must use PDO with these two settings, because they make SQL injection
much harder and make errors visible instead of silent:

```php
$pdo = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
```

- [ ] Add `api/config.php` (real password) to `.gitignore`, and commit
      `api/config.example.php` with fake values. **Never commit the DB password.**

**Phase 2 — Sep 14–20 — you own the search endpoint, worth 5 points on its own**
- [ ] `api/searchContacts.php`. Partial match, scoped to the logged-in user:

```php
$term = "%" . $input['search'] . "%";
$stmt = $pdo->prepare(
  "SELECT ContactID, FirstName, LastName, Phone, Email FROM Contacts
   WHERE UserID = ?
     AND (FirstName LIKE ? OR LastName LIKE ? OR Phone LIKE ? OR Email LIKE ?)
   ORDER BY LastName, FirstName"
);
$stmt->execute([$_SESSION['userId'], $term, $term, $term, $term]);
```

The `%` on both sides is what makes `smi` find `Smith`. An empty search string
becomes `%%`, which returns everything — that's the desired behavior for showing
the full list.

- [ ] Test it: log in as user A, search for a contact you know belongs to user B,
      confirm you get zero results. Screenshot this — it's demo material.

**Phase 3 — Sep 21–26 — the ERD (5 rubric points)**
- [ ] Draw the ERD in **draw.io** (free, exports PNG). Show both tables, every
      column with its type, the primary keys, and the one-to-many line from Users
      to Contacts with proper crow's-foot notation.
- [ ] Export as PNG, commit to `docs/erd.png`, send to Sam for the slides.

**Presentation:** you explain the database design and the search query. You should
be able to answer "why is it slow if you have a million contacts?" (answer: `LIKE
'%term%'` can't use a normal index — mention full-text indexes exist). Instructors
ask that.

---

### Sebastian — API Dev

You own authentication and the Swagger documentation.

**Phase 1 — Sep 9–13**
- [ ] Install **Postman** (free). This is how you test an API without a UI. You do
      not need the front end to exist to do your job.
- [ ] Write `api/helpers.php` — three small functions everyone else uses:
  - `getJsonInput()` — reads the request body and `json_decode`s it
  - `sendJson($array)` — sets `Content-Type: application/json` and echoes it
  - `requireLogin()` — if `$_SESSION['userId']` isn't set, send an error and `exit`
- [ ] Coordinate with Arwa on this file's function names **before** writing it. She
      calls these functions; if you rename one later, her endpoints break.

**Phase 2 — Sep 14–20**
- [ ] `api/register.php` — validate fields aren't empty, check the username isn't
      taken, `password_hash()` the password, insert, return the new user.
- [ ] `api/login.php` — look up by login, `password_verify()`, set
      `$_SESSION['userId']`, return the user.
- [ ] `api/logout.php` — `session_destroy()`.
- [ ] Error cases to handle, because the demo will hit them: duplicate username,
      wrong password, empty fields, missing JSON body.
- [ ] Return a **generic** "Invalid username or password" for both a bad username
      and a bad password. Saying "that user doesn't exist" tells an attacker which
      usernames are real.

**Phase 3 — Sep 21–26 — SwaggerHub (5 rubric points)**
- [ ] Make a free SwaggerHub account, create an OpenAPI 3.0 spec.
- [ ] Document exactly **two** endpoints — no more, the assignment caps it. Use
      `login.php` and `searchContacts.php`; search is the most impressive one.
- [ ] For each: the path, method, request body schema, and both a success and an
      error response example.
- [ ] Point the spec's server URL at the real live domain and use SwaggerHub's
      "Try it out" to fire a real request. **At least one endpoint must actually
      work live during the presentation.** Test it the day before.
- [ ] Commit the spec as `docs/openapi.yaml`.

**Phase 3 — Sep 21–26 — Sequence diagram (part of the 5 diagram points)**
- [ ] Draw the sequence diagram for "user searches for a contact": Browser →
      contacts.js → searchContacts.php → MySQL → back up the chain. You wrote the
      API, so you know these arrows better than anyone. draw.io, export PNG to
      `docs/sequence-diagram.png`.

**Presentation:** you demo SwaggerHub live and explain password hashing and sessions.

---

### Arwa — API Dev + Front End

You're the bridge. You write the CRUD endpoints *and* the JavaScript that calls
them, which means you're the one who catches contract mismatches early.

**Phase 1 — Sep 9–13**
- [ ] Agree the JSON contract (section 1) with Sebastian and John. Write it into
      `docs/api-contract.md` and commit it. That document is what stops arguments.

**Phase 2 — Sep 14–17 — three endpoints, all following the same pattern**
- [ ] `api/addContact.php` — insert with `UserID = $_SESSION['userId']`.
- [ ] `api/editContact.php` — `UPDATE ... WHERE ContactID = ? AND UserID = ?`.
- [ ] `api/deleteContact.php` — `DELETE ... WHERE ContactID = ? AND UserID = ?`.

That `AND UserID = ?` on edit and delete is not optional. Without it, user A can
delete user B's contacts by guessing an ID number. Add it every time.

**Phase 2 — Sep 17–20 — `js/contacts.js`, the main front-end logic**
- [ ] One reusable helper so you write the fetch boilerplate once:

```javascript
async function apiCall(endpoint, payload) {
  const res = await fetch('api/' + endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',        // sends the session cookie — required
    body: JSON.stringify(payload)
  });
  return res.json();
}
```

- [ ] Wire up: load contacts on page load, add, edit, delete, and search.
- [ ] **Search must hit the server on every keystroke** — you may not filter a
      cached array in JavaScript. The assignment forbids it and it's 5 points.
- [ ] Add **debouncing** so you don't fire a request per character. Wait ~300ms
      after typing stops:

```javascript
let timer;
searchBox.addEventListener('input', () => {
  clearTimeout(timer);
  timer = setTimeout(() => doSearch(searchBox.value), 300);
});
```

This is a genuinely good detail to point out in the presentation — it shows you
thought about load, and it's a strong candidate for the discretionary points.

- [ ] Redirect to `index.html` if any API call returns a "not logged in" error.

**Phase 3 — Sep 21–26**
- [ ] **Activity diagram** (part of the diagram points): flowchart of "add a
      contact" — including the branch where validation fails. `docs/activity-diagram.png`.

**Presentation:** you explain how the front end talks to the back end — the AJAX
call, the JSON round trip, and why the search is server-side.

---

### John — Front End

You own everything the user sees. Your work is what gets demoed for 20 points, so
it needs to look finished.

**Phase 1 — Sep 9–13**
- [ ] `index.html` — login form and register form on one page, toggling between
      them with a link. This is the landing page; the assignment requires that
      login/register is the first thing a user sees.
- [ ] `contacts.html` — search bar at top, contact list/table below, an "Add
      Contact" button, and Edit/Delete on each row.
- [ ] Use **real form semantics**: `<form>`, `<label>` attached to every `<input>`
      via `for`/`id`, `<button type="submit">`. Not a pile of `<div>`s. This is
      most of your accessibility score, and it's easier than the alternative.
- [ ] A visible spot for error messages — the demo will need to show a failed
      login, and "nothing happened" looks broken.

**Phase 2 — Sep 14–20**
- [ ] `css/style.css`. Keep it simple and consistent: one font, a small color
      palette, generous spacing. A clean plain design scores better than an
      ambitious messy one.
- [ ] Make it work on a phone-sized window. The instructor may resize the browser.
- [ ] `js/login.js` — hook the two forms up to `register.php` and `login.php`,
      show errors, redirect to `contacts.html` on success. Same `apiCall` helper
      Arwa wrote; don't write a second one.

**Phase 3 — Sep 21–24 — Lighthouse accessibility report (5 points, easy to max out)**
- [ ] Chrome → F12 → Lighthouse tab → check **Accessibility** → Analyze. Run it on
      the **live domain**, not a local file.
- [ ] Fix what it flags. The usual list, all quick:
  - every input has a real `<label>`
  - text/background contrast ratio ≥ 4.5:1
  - `<html lang="en">`
  - a `<title>` on every page
  - icon-only buttons need `aria-label`
  - headings go `h1` → `h2` → `h3` without skipping
- [ ] Re-run until you're at **95+**. Screenshot the score for the slides and save
      it as `docs/lighthouse.png`.
- [ ] Also tab through the whole app with only the keyboard. If you can't reach a
      button, that's a bug.

**Presentation:** you drive the live app demo and present the Lighthouse score.
Rehearse the demo clicks until they're muscle memory.

---

### Sam — Project Manager + Database

You own infrastructure, the schedule, and the deck. Your job is that nobody is
blocked and nothing is missing at the deadline.

**Phase 1 — Sep 9–13 — infrastructure. The domain part is due TODAY.**
- [ ] Create the GitHub repo, add all 5 members as collaborators, push a README.
- [ ] Protect `main`: Settings → Branches → require a pull request before merging.
      This forces the code reviews the rubric asks for.
- [ ] Create the DigitalOcean droplet. Use the **LAMP marketplace image** — it
      arrives with Apache, MySQL, and PHP already installed and saves hours.
      GitHub Student Pack gives you $200 in credit.
- [ ] **Buy the domain today.** DNS takes up to 48 hours to propagate, and an IP
      address is explicitly not acceptable. Namecheap is ~$2 for a `.xyz`; the
      Student Pack includes a free `.me`. Point the A record at the droplet IP.
- [ ] Install a free SSL cert: `sudo certbot --apache`. Takes five minutes and
      makes the site `https://`. An HTTP-only login form is a visible deduction
      under "current standards."
- [ ] Give Noah MySQL access and everyone else deploy access.

**Phase 1 — Sep 10–13 — deployment**
- [ ] Set up deployment so anyone can ship: `git clone` the repo onto the droplet
      into `/var/www/html`, and to deploy, SSH in and `git pull`. Write these exact
      commands in `docs/deploy.md` so you're not the bottleneck.
- [ ] Confirm Apache serves `index.html` at the domain before anyone writes real code.

**All 18 days — running the team**
- [ ] Set up the Discord server with `#general`, `#frontend`, `#backend`, `#help`.
- [ ] **Gantt chart (5 rubric points).** Already built — see [docs/gantt.md](docs/gantt.md).
      *Update it as things slip.* A Gantt chart matching reality is more impressive
      than a fictional one.
- [ ] **Daily** 10-minute standup, not twice-weekly. Three questions each: what did
      you finish, what's next, what's blocking you. On an 18-day project a blocker
      that sits for three days has eaten a sixth of the schedule.
- [ ] **Check the GitHub Insights → Contributors graph every 2–3 days.** If someone
      has no commits, talk to them privately that day. 25 points are on the line and
      it is unrecoverable at the end.

**Phase 2 — Sep 14–20 — you're the second database person**
- [ ] Pair with Noah on schema and review every SQL query for the missing
      `WHERE UserID = ?`. That's the highest-risk bug in this project.
- [ ] Set up a nightly `mysqldump` backup. If the DB dies the day before the
      presentation, this saves the project.
- [ ] **Use case diagram** (part of the diagram points): actors are User and
      System; use cases are Register, Login, Add/Edit/Delete/Search Contact.
      `docs/use-case-diagram.png`.

**Phase 3 — Sep 21–26 — the deck**
- [ ] Assemble the PowerPoint. Sections required by the assignment: title, team
      members and roles, technology used, what went well, what didn't, Gantt, ERD,
      API demo, app demo, questions.
- [ ] For "what didn't go well," be honest and specific — "we lost a day to CORS
      errors and fixed it by serving the API from the same origin" reads as
      competent. "Nothing went wrong" reads as untrue.
- [ ] **Every member submits the slides to WebCourses individually.** If one person
      forgets, that person gets a zero. Send the file in Discord and get five
      thumbs-up reactions before the deadline.
- [ ] Add project title, GitHub URL, and live domain to the signup spreadsheet
      before presenting.
- [ ] **Test the domain on UCF campus WiFi two days before and one day before.**
      UCF IT occasionally blocks new domains and it takes them a day or two to
      unblock. This is called out in the assignment because it has actually
      happened to teams.
- [ ] Put the slides on a **USB drive**. No cloud retrieval time during the slot.

**Presentation:** you open with the project overview, team, and tech stack, and
present the Gantt chart. Keep the whole team under 12 minutes.

---

## 4. Timeline

Full dated schedule with every task, owner, and dependency: **[docs/gantt.md](docs/gantt.md)**.

**You have 18 days: Wed Sep 9 through Sat Sep 26. Everything is due Sun Sep 27.**

| Phase | Dates | Days | Goal | Definition of done |
|-------|-------|------|------|--------------------|
| 1 | Sep 9–13 | 5 | Foundation | Domain resolves over HTTPS, DB exists, repo has HTML pages, everyone has pushed at least one commit |
| 2 | Sep 14–20 | 7 | Build the app | All 8 endpoints pass in Postman **and** the full flow works in a browser on the live site |
| 3 | Sep 21–26 | 6 | Docs, polish, rehearse | Lighthouse 95+, ERD/Swagger/diagrams done, deck done, two timed run-throughs |
| — | **Sep 27** | — | **DUE** | Presentation + all 5 submissions in WebCourses |

**Deadline that sneaks up on people:** the domain must be bought **today**. DNS
propagation can take 48 hours and is not something you can rush at the end. On an
18-day schedule, losing two days to DNS is losing 11% of the project.

**There is no buffer week.** The original four-week shape had a spare week for
things going wrong; this schedule does not. Phase 2 is the only place with any
slack, and it is thin. If you are still writing endpoints on Sep 21, cut scope on
the UI — not on the demo, the search, or the diagrams, all of which are graded
directly.

---

## 5. Rubric coverage — who owns each point

| Item | Pts | Owner |
|------|-----|-------|
| PowerPoint submitted on time | 5 | **Everyone submits individually** |
| Professional slides | 5 | Sam |
| All members participate | 5 | Everyone |
| Gantt chart | 5 | Sam |
| ERD | 5 | Noah |
| Use case / Activity / Sequence diagrams | 5 | Sam / Arwa / Sebastian |
| SwaggerHub demo | 5 | Sebastian |
| Server-side partial-match search | 5 | Noah + Arwa |
| Lighthouse accessibility | 5 | John |
| Working demo | 20 | Everyone |
| Current standards | 5 | Everyone (HTTPS, hashed passwords, prepared statements) |
| Discretionary excellence | 5 | Debounced search, HTTPS, clean UI, honest retro |
| GitHub contribution | 25 | **Everyone, every 2–3 days, starting Sep 9** |

---

## 6. Ways teams lose points on this project

- **Backloaded commits.** One person pushing everything in the last three days
  costs the other four most of a 25-point category.
- **Search filtered in JavaScript.** Explicitly forbidden. It must hit the server.
- **Forgetting `WHERE UserID = ?`.** Users can see each other's contacts, which
  fails a core requirement and is obvious in a live demo.
- **Demoing on `localhost` or a raw IP.** Both are disallowed.
- **The DB password committed to GitHub.** Use `.gitignore` from the first commit,
  not after.
- **Running over 12 minutes.** Time every rehearsal with an actual timer.
- **One person not submitting the slides.** That's an individual zero.
