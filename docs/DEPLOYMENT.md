# Deploying this safely: frontsite first, ERP later

Your situation: **zaheerah.com is already live**, the ERP is not deployed yet, and
both codebases now carry changes. Here is the order that works.

## The short answer

**Deploy the frontsite today. It does not need the ERP to exist.**

The integration is built to switch itself off when it is not configured. With no
ERP settings, every ERP call becomes a no-op: the shop runs from its own data,
and every order placed in the meantime is quietly marked as "not yet pushed".
When you deploy the ERP and flip one switch, those orders are pushed across
automatically. Nothing is lost in between.

```
now ──────────────► frontsite live (ERP off)
                     orders sell, get queued
                     │
later ────────────► ERP deployed
                     │
flip the switch ──► queued orders pushed, catalogue syncs both ways
```

---

## Why deploying the frontsite alone is safe

`ErpClient::enabled()` requires a base URL **and** a key:

```php
return (bool) config('services.erp.enabled')
    && filled(config('services.erp.base_url'))
    && filled(config('services.erp.key'));
```

With neither set, this is what happens on the live site:

| Feature | Behaviour without the ERP |
|---|---|
| Catalogue sync | No-op. The shop keeps using its own 28 products. |
| Paid order | Order is saved and paid as normal. `erp_sale_id` stays null, so it is queued. |
| Contact form / quote list | Enquiry is saved and emailed. ERP forwarding is skipped. |
| Footer "Staff Login" link | Hidden, because `ERP_STAFF_LOGIN_URL` is empty. |
| Scheduled sync task | Exits cleanly saying sync is switched off — no error noise. |

All of the above is covered by tests, including the checked-out state of a site
with the ERP switched off.

---

## Phase 1 — deploy the frontsite now

### Server `.env` (`zaheerah/.env`)

```env
APP_NAME="Zaheerah Ventures"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://zaheerah.com
LOG_LEVEL=error

# Leave the ERP block out entirely for now. That is the "off" switch.
```

`APP_DEBUG=true` on a live site leaks configuration and stack traces, and your
local file currently has it on.

### Commands

```bash
cd /path/to/zaheerah
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

The migration is **purely additive** — nullable columns on `products`, `orders`,
`quote_requests` and `contacts`. No existing row is modified.

If product images 404, the storage link is missing (your local Windows junction
does not travel to Linux):

```bash
php artisan storage:link
```

### What to verify

| Check | Expected |
|---|---|
| `https://zaheerah.com/` | Loads |
| `/shop` | Product grid plus filter buttons listing your real categories |
| A product image URL | Serves the image (was broken before) |
| `/contact` — submit a real message | "Thank you for your message" ← **this is broken in your current deploy** |
| `/admin/products/images` | Loads ← **this 500s in your current deploy** |
| Place a small test order | Payment completes, and the order gets a confirmation email |

> **Before you tell anyone about `/admin/products/images`:** that page is
> unauthenticated. Put it behind a login or an IP allowlist, or keep the URL to
> yourself. It is a public file-upload endpoint today.

---

## Phase 2 — deploy the ERP

Put it on a subdomain, e.g. `erp.zaheerah.com`, on HTTPS. The ERP holds payroll
and cost prices, so it should not be casually public.

```bash
cd /path/to/zaheera-erp
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan key:generate          # only if APP_KEY is empty
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link          # required for product images to be served
npm ci && npm run build           # required: the admin UI is compiled assets
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

`db:seed` creates the categories, roles, the **full 28-product catalogue with its
images**, and 5 staff accounts. It is idempotent, so re-running it is safe.

`db:seed` also creates the Bloom page pricing permissions
(`view_bloom_pricing`, `manage_bloom_pricing`) used by the `/bloom-tiers` and
`/bloom-products` admin screens; the Super Admin role receives both. Those two
screens are the source of truth for the pricing shown on the frontsite's
`/zaheerah-bloom` page.

> **Seeded products start at stock 0.** The opening quantity is a real business
> figure, so the seeder does not invent one. Until you enter stock in the admin,
> those products read as "Out of Stock" on the website.

The product images ship in `database/seeders/images/products/` and are copied
onto the ERP's public disk as the seeder runs, so a seeded product is
self-contained. That is why `php artisan storage:link` matters.

### Two steps people forget on the ERP

* **`npm run build`** — this is a Vue/Inertia app and `public/build` is
  gitignored, so it is **not** in the repository. Without a build on the server
  the admin UI will not load. Run it on the server (needs Node) or build locally
  and upload `public/build`.
* **`php artisan storage:link`** — product images are uploaded to
  `storage/app/public`. Without the link they exist but are not reachable, so
  every product image on the website will 404.

### Three things that specifically matter for the ERP

1. **Never copy `bootstrap/cache/*.php` from your PC.** That is exactly what broke
   your local ERP: the cached config held absolute Windows paths, including the
   database path. Deploy from git — those files are not committed, so a fresh
   clone is clean. If anything ever behaves as if config changes are ignored,
   delete `bootstrap/cache/*.php` and retry.

2. **The seeded staff passwords are public knowledge.** The seeder creates
   `admin@gmail.com`, `manager@gmail.com`, `employee@gmail.com` and two others
   with the password `123456789`. Change them all, and set `can_login`
   deliberately per account.

3. **Decide where the database lives.** You are on SQLite. It works for a handful
   of staff, but a redeploy that overwrites the file loses your business data:
   * keep `database/database.sqlite` **outside** the deploy directory (set
     `DB_DATABASE=/var/lib/zaheerah/erp.sqlite`), and
   * back it up nightly.
   If several staff use it at once, move to MySQL or Postgres — the ERP's `.env`
   already has a commented-out Postgres block.

### Set the shared secret

```bash
php -r "echo bin2hex(random_bytes(24));"
```

Put the result in the ERP `.env`:

```env
STOREFRONT_API_KEY=<the generated secret>
```

---

## Phase 3 — connect the two

**ERP `.env`:** `STOREFRONT_API_KEY` (done above).

**Frontsite `.env`:**

```env
ERP_BASE_URL=https://erp.zaheerah.com
ERP_API_KEY=<the same secret>
ERP_TIMEOUT=10
ERP_SYNC_ENABLED=true
ERP_STAFF_LOGIN_URL="${ERP_BASE_URL}/login"
```

> **Both URLs need their scheme.** `ERP_BASE_URL=erp.zaheerah.com` is not just
> untidy: cURL assumes plain **http** for a scheme-less URL, so the shared API key
> would travel unencrypted over port 80. `php artisan erp:check` now refuses to
> run without a scheme, and warns when it is not https. A scheme-less
> `ERP_STAFF_LOGIN_URL` makes the footer link resolve relative to the site.

Then on **both** apps, so the cached config picks up the new values:

```bash
php artisan optimize:clear && php artisan config:cache
```

Check the link from the frontsite:

```bash
cd /path/to/zaheerah
php artisan erp:check
```

Expected:

```
Connected to: Zaheerah Ventures
ERP reports 28 product(s) in the catalogue.
This site has 0 product(s) linked to the ERP.
```

If you get `401`, the two keys do not match. If you get a connection error,
check `ERP_BASE_URL` and that the ERP is reachable from the web server.

Also note: **give each app its own `APP_KEY`.** Never copy one `.env` over the
other; sharing an `APP_KEY` between two apps undermines session and cookie
security.

---

## Phase 4 — first sync and the back-fill

Run these on the frontsite, in this order:

```bash
# 1. hand your existing shop products to the ERP (preview, then apply)
php artisan erp:publish-products --category=Trade --stock=0
php artisan erp:publish-products --category=Trade --stock=0 --force

# 2. pull the ERP catalogue back down so the shop shows live stock
php artisan erp:sync-catalog

# 3. back-fill the orders that were paid while the ERP was offline  ← the important one
php artisan erp:push-pending-orders
```

Step 3 is what makes the "deploy the frontsite first" plan safe. Every order that
came in during the gap becomes a real ERP sale with its stock deducted.

> **Watch `--stock`.** With `--stock=0`, the 28 imported products read as
> **Out of Stock** on the site until you enter real figures in the ERP. If you
> know the opening quantities, pass `--stock=N`. You can also skip step 1
> entirely and only push the ERP's own products down.

**About images.** Step 1 also pulls each product's image **file** into the ERP —
the ERP downloads it and stores its own copy, so the imported products are
completely inside the ERP and do not depend on the shop still hosting them. If a
download fails the link is kept and printed as a warning, so the product is still
created; fix those images on the ERP product form afterwards.

From then on, upload images on the ERP product form — the shop picks them up on
the next sync. A product whose image has not been uploaded yet keeps whatever the
shop already has, so nothing is blanked out in the meantime.

> `erp:publish-products` needs the frontsite to be reachable from the ERP server
> for the image download, and `APP_URL` on the frontsite must be correct or the
> URLs it sends will point at the wrong host.

---

## Phase 5 — cron, so it keeps itself in sync

On the frontsite server:

```
* * * * * cd /path/to/zaheerah && php artisan schedule:run >> /dev/null 2>&1
```

That runs the catalogue sync every 15 minutes and retries unpushed orders every
5 minutes. Without cron the integration still works — you just have to run those
two commands by hand after changing prices or stock.

---

## The whole thing in one line

```
frontsite (sync off) → verify → ERP → connect → publish + sync + back-fill → cron
```

---

## Pre-launch checklist

| Item | Why it matters |
|---|---|
| `APP_DEBUG=false` on both | Debug pages expose config, secrets and stack traces. |
| `APP_URL` set correctly on both | Wrong URLs break redirects, Paystack callbacks and links. |
| **Paystack keys** | Your local `.env` has **test** keys (`pk_test_…`). If production still uses them, no real money can be taken. Confirm the server has **live** keys. |
| Paystack callback URL | Must be the live `https://zaheerah.com/checkout/callback`. |
| `MAIL_MAILER` on the frontsite | Local is `log`, so mail goes to a file. Set real SMTP, or customers get no confirmation — and that email is currently broken in production anyway. |
| **`npm run build` on the ERP** | `public/build` is not in git. No build, no admin UI. |
| **`php artisan storage:link` on the ERP** | Without it every product image 404s. |
| Products created in the ERP admin | That is now the only place to add products or their images. |
| Seeded ERP passwords changed | `123456789` is in the repo. |
| WooCommerce keys in the frontsite `.env` | `WC_KEY` / `WC_SECRET` look unused. If they are, remove them; if they are live credentials, rotate them. |
| Old DB password | A real Postgres password sits commented out in the ERP `.env`. Rotate it if it was ever live. |
| `/admin/products/images` | Public upload endpoint. Protect it. |
| HTTPS on the ERP | The shared secret travels in a request header. |
| Nightly database backups | Both apps. The ERP holds payroll and stock history. |

---

## If something goes wrong

| Symptom | Fix |
|---|---|
| Config changes appear ignored | `bootstrap/cache/*.php` is stale. Delete those files and retry. |
| `ERP_BASE_URL has no scheme` | Add `https://`. Without it cURL silently uses plain http and the API key is sent unencrypted. |
| `401` from the ERP | Keys differ. Fix both `.env` files, then `php artisan config:cache` on each. |
| Footer "Staff Login" 404s | `ERP_STAFF_LOGIN_URL` is missing its `https://`, so the link resolves relative to the site. |
| Shop shows "Out of Stock" everywhere | Expected after `--stock=0`. Enter stock in the ERP, then `php artisan erp:sync-catalog`. |
| Orders have `erp_push_error` | Run `php artisan erp:push-pending-orders` and read the message. |
| Products appear twice on the site | A product was linked twice. Check `erp_product_id` uniqueness on `products`. |
| The sync tries to create products the shop already has | The ERP catalogue shares their names but the shop rows are unlinked. The sync adopts unlinked rows by name, so this means the names differ — compare them in the ERP admin. |

**Rollback:** both apps deploy from a git branch, so `git checkout <previous-commit>`
plus `composer install` restores the previous code. You do **not** need to roll
back the migrations — the new columns are nullable and simply ignored by the old
code, so the site keeps working either way.

---

## Do this first, locally

Both repos have uncommitted work:

```bash
cd zaheerah && git status          # your integration + bug fixes
cd ../zaheerah-epr/zaheerah-epr && git status   # your in-progress work + ERP API
```

Commit and push both, frontsite first. Everything above assumes the server is
pulling from those branches.
