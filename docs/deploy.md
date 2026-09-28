# Deploying the app

The droplet serves `/var/www/html` directly with Apache — there is no build step.
Deploying means getting the latest `main` onto the server.

## One-time setup (already done on the droplet)

```bash
ssh <deploy-user>@<DROPLET_IP>
sudo rm -rf /var/www/html          # clear the Apache placeholder page
sudo git clone https://github.com/samborg-dev/COP4331-Small-Project.git /var/www/html
cd /var/www/html
sudo cp api/config.example.php api/config.php
sudo nano api/config.php           # fill in the real DB host/name/user/pass
sudo chown -R www-data:www-data /var/www/html
```

`api/config.php` is gitignored on purpose — it holds the real database
password and is never pushed. Every fresh checkout needs this copy-and-edit
step once.

## Every deploy after that

```bash
ssh <deploy-user>@<DROPLET_IP>
cd /var/www/html
sudo git pull
```

That's it — no restart needed, Apache serves the updated PHP/HTML/JS on the
next request. If a deploy adds a new file that needs specific permissions
(rare), re-run `sudo chown -R www-data:www-data /var/www/html`.

## Verifying a deploy

```bash
curl -I https://<DOMAIN>/            # expect HTTP/2 200
curl -I https://<DOMAIN>/api/login.php   # expect a response, not a 404/500
```

Check `sudo tail -f /var/log/apache2/error.log` if something 500s after a
pull — that log is the fastest way to see a PHP fatal error that curl won't
show you.

## Rolling back

If a `git pull` brings down a broken `main` (shouldn't happen if PRs are
reviewed before merge, but just in case):

```bash
cd /var/www/html
sudo git log --oneline -5     # find the last good commit
sudo git reset --hard <good-commit-sha>
```

Only do this on the droplet's working copy — never force-push `main` on
GitHub to "fix" this. Fix forward with a new PR instead.

---

_Sam — fill in `<deploy-user>`, `<DROPLET_IP>`, and `<DOMAIN>` with the real
values once the droplet exists, and drop the deploy-user's SSH key setup
notes here if it's not the default `root`._
