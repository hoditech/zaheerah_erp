# How the Zaheerah frontsite and ERP work together

> **Already live with only the frontsite deployed?** See `DEPLOYMENT.md` for the
> safe rollout order. Short version: the frontsite can go live on its own — with
> no ERP settings every ERP call is a no-op, and orders placed in the gap are
> pushed across automatically once you switch the integration on.

Two separate Laravel applications, connected by one small, secret-key-guarded
JSON API. The ERP is the hub: it owns products, prices, stock and sales. The
frontsite owns the public shop, imagery and marketing copy, and sends paid
orders and enquiries back to the ERP.

```
        Browser
           |
   +-------+--------------------------+
   |                                  |
   v                                  v
FRONTSITE  (zaheerah/)            ERP  (zaheerah-epr/zaheerah-epr/)
Laravel 12, Blade + Alpine        Laravel 11, Inertia + Vue, Spatie roles
public shop + checkout            inventory, POS, reports, payroll
SQLite: zaheerah/database/...     SQLite: zaheerah-epr/.../database/...
   |                                  ^
   |  GET  /api/v1/catalog/products   |  products, prices, live stock
   |  POST /api/v1/orders             |  paid order -> sale + stock deduction
   |  POST /api/v1/leads              |  quote requests + contact messages
   +----------------------------------+
        header: X-Storefront-Key
```

The two databases stay separate and are never joined. They talk over HTTP only.

---

## 1. Why it is built this way

* **The databases cannot be merged.** Both apps define `products` and `users`
  with different shapes, and SQLite cannot query across files the way MySQL can.
  Sharing one database would mean renaming tables and rewriting foreign keys.
* **The ERP should never be exposed.** It holds payroll, costs and staff
  accounts, so the public site reaches it through one narrow, authenticated API
  rather than a shared database.
* **Both apps still deploy and develop independently**, which is how they are
  already being built.

---

## 2. Who owns what

Products are created and edited **only in the ERP admin**. The shop displays them.

| Data | Owner | How it moves |
|---|---|---|
| Product name, price, category, stock | **ERP** | `erp:sync-catalog` pulls it down |
| Product image | **ERP** | Uploaded on the product form, or downloaded and stored when a product is imported from the shop. Synced down to the site |
| Badge, sort order, description, visibility | **Frontsite** | Never overwritten by a sync |
| Web sales + stock deduction | **ERP** | Paid order pushed from checkout |
| Quote requests, contact messages | **Frontsite** stores, **ERP** works them | Pushed on submit, visible at `/online-leads` |

### Adding a product

1. In the ERP admin: **Products → Add Product**, fill in name, price, unit cost,
   opening stock, category, and upload the image.
2. Run `php artisan erp:sync-catalog` on the frontsite (or wait for the scheduler,
   which runs it every 15 minutes).

The product appears in the shop with its price, live stock and image.

The image field lives on the ERP product (`products.image_url`) and stores either
`storage/products/<file>` for an upload, or an absolute URL when an image could
not be fetched. The catalogue API returns it as an absolute URL, so the frontsite
stores it exactly as received.

### Images are owned by the ERP, files included

When a product is imported from the shop (`erp:publish-products`), the ERP does
not just record a link — it **downloads the image and stores its own copy** on
its public disk. An imported product is therefore complete inside the ERP and
does not depend on the shop still hosting the file.

The import is deliberately forgiving, because a missing image should never cost
you the product:

| Situation | What happens |
|---|---|
| Image downloads fine | Stored on the ERP disk; the product points at the ERP's own copy |
| Download fails or 404s | The original link is kept and reported in `warnings`, so you can see and fix it |
| The file is not an image | Rejected (checked by content type) and the link is kept |
| Larger than 2 MB | Kept as a link rather than stored |
| Already a relative path, or already on the ERP | Stored as-is, no download |

After the import, the ERP's own URL is what syncs back down to the shop, so the
site then displays images served by the ERP.

> The frontsite's `/admin/products/images` page still exists for products whose
> images have not been moved into the ERP yet. It is **unauthenticated** — protect
> it or stop using it once every product has an ERP image.

---

## 3. Configuration

Both `.env` files must hold the **same** secret.

**ERP** (`zaheerah-epr/zaheerah-epr/.env`):

```env
STOREFRONT_API_KEY=<same-value-as-ERP_API_KEY>
```

**Frontsite** (`zaheerah/.env`):

```env
ERP_BASE_URL=http://127.0.0.1:8001
ERP_API_KEY=<same-value-as-STOREFRONT_API_KEY>
ERP_TIMEOUT=10
ERP_SYNC_ENABLED=true
ERP_STAFF_LOGIN_URL="${ERP_BASE_URL}/login"
```

Generate a secret with:

```bash
php -r "echo bin2hex(random_bytes(24));"
```

Setting `ERP_SYNC_ENABLED=false` turns the frontsite into a standalone shop
again: every ERP call becomes a no-op and nothing breaks.

---

## 4. Running both apps locally

Port 8000 is already taken on this machine by another project, so the frontsite
uses 8080 here.

```bash
# terminal 1 - the ERP
cd "zaheerah-epr/zaheerah-epr"
php artisan serve --port=8001

# terminal 2 - the frontsite
cd zaheerah
php artisan serve --port=8080

# terminal 3 - the scheduler (runs the syncs)
cd zaheerah
php artisan schedule:work
```

Then check the wiring:

```bash
cd zaheerah
php artisan erp:check
```

Expected output:

```
Connected to: Zaheerah Ventures
ERP reports 28 product(s) in the catalogue.
This site has 0 product(s) linked to the ERP.
```

---

## 5. The commands

Run all of these in `zaheerah/` (the frontsite).

| Command | What it does |
|---|---|
| `php artisan erp:check` | Verifies configuration and connectivity. Run this first when something looks wrong. |
| `php artisan erp:sync-catalog` | Pulls products, prices and stock from the ERP. Add `--dry-run` to preview, `--prune` to hide products the ERP dropped. |
| `php artisan erp:publish-products` | One-time import: pushes frontsite-only products **into** the ERP and links them. Dry run unless `--force`. |
| `php artisan erp:push-pending-orders` | Re-sends paid orders the ERP never received. |

Scheduled automatically (see `routes/console.php`), and only while
`php artisan schedule:work` or a production cron is running:

* `erp:sync-catalog` every 15 minutes
* `erp:push-pending-orders` every 5 minutes

### First-time setup order

```bash
cd zaheerah

# 1. link the shop to the ERP catalogue
php artisan erp:sync-catalog

# 2. import the 28 existing shop products into the ERP (preview first)
php artisan erp:publish-products --category=Trade --stock=0
php artisan erp:publish-products --category=Trade --stock=0 --force

# 3. re-sync so the shop reflects what the ERP now holds
php artisan erp:sync-catalog
```

> **Heads-up on `--stock`.** Newly imported products start at whatever you pass.
> With the default of `0` they read as **Out of Stock** on the site until you
> enter real figures in the ERP. Pass `--stock=N` if you know the opening
> quantity, or accept `0` and fill stock in later.

---

## 6. What happens on a paid order

1. Paystack calls `CheckoutController::callback`.
2. The order is marked `paid`, then pushed to the ERP as a sale.
3. The ERP records a `sales` row (`channel = web`, `external_ref` = your order
   number), the line items, and deducts stock.
4. The order stores `erp_sale_id` and `erp_receipt_number` for traceability.

Rules the ERP follows, because the customer has already paid:

* **Idempotent.** Paystack can call the callback more than once. A repeat order
  number returns the original sale instead of booking a second one.
* **Never rejects an order.** An unknown product is created automatically
  (category `General`) and reported in `warnings`.
* **Never lets stock go negative.** A shortage is capped at 0 and reported.
* **Totals are recalculated server-side**, so a tampered client amount is ignored.

Any warnings are written to both `orders.erp_push_error` and the ERP activity
log, so nothing fails silently.

If the ERP is down during checkout, the confirmation page still works. The order
is retried automatically within 5 minutes, or on demand:

```bash
php artisan erp:push-pending-orders
```

---

## 7. Seeing the enquiries in the ERP

Staff open **`/online-leads`** in the ERP (linked from the footer of the site as
"Staff Login"). The page lists quote requests and contact messages, and lets you
mark each one `new`, `contacted` or `archived`.

It is a plain Blade page, so it needs no JavaScript build step.

---

## 8. API reference

All routes require the header `X-Storefront-Key: <secret>`.
Base path: `/api/v1`.

| Method | Path | Purpose |
|---|---|---|
| GET | `/ping` | Health and connectivity check |
| GET | `/catalog/products` | List products (optional `?updated_since=ISO8601`) |
| POST | `/catalog/products` | Create or update a product by name |
| POST | `/orders` | Record a paid order as a sale |
| POST | `/leads` | Record a quote request or contact message |
| GET | `/leads` | List enquiries (`?status=`, `?type=`) |

A wrong or missing key returns `401`.

---

## 9. Going live

* Put the site on `zaheerah.com` and the ERP on `erp.zaheerah.com`, or use two
  separate hosts. Then set `ERP_BASE_URL=https://erp.zaheerah.com` and update
  `ERP_STAFF_LOGIN_URL`.
* Use **HTTPS** for the ERP. The shared key travels in a header.
* Add the cron entry so the syncs actually run:
  `* * * * * cd /path/to/zaheerah && php artisan schedule:run >> /dev/null 2>&1`
* Rotate the key by changing it in both `.env` files and running
  `php artisan config:clear` on each.
* Remember the ERP has **no `config/sanctum.php`** and no `routes/api.php` of its
  own beyond the new one; the storefront API deliberately uses a shared key
  instead of Sanctum tokens, because it is server-to-server and there is no user
  to log in.

---

## 10. Troubleshooting

| Symptom | Cause and fix |
|---|---|
| `erp:check` says "Could not reach the ERP" | The ERP is not running, or `ERP_BASE_URL` is wrong. |
| `401 Unauthorized` | The keys differ. They must match exactly, then `php artisan config:clear` on both apps. |
| Everything 404s, or "There is no existing directory at ..." | A **stale `bootstrap/cache/config.php`** from an old project location. Delete the files in `bootstrap/cache/` (except `.gitignore`) and retry. |
| Product images 404 | `public/storage` is a broken link. Recreate it: `cmd /c rmdir public\storage` then `New-Item -ItemType Junction -Path public\storage -Target "$PWD\storage\app\public"`. |
| A synced product shows Out of Stock | Its ERP stock is 0. Set the real figure in the ERP and re-sync. |
| Order shows `erp_push_error` | Read the message, fix the cause, run `erp:push-pending-orders`. |

---

## 11. Bugs fixed while wiring this up

1. **The ERP could not run at all.** `bootstrap/cache/config.php` still pointed
   at the deleted `Documents\laravel\zaheerah-epr\...` path, including the
   database file. Cleared the stale cache.
2. **All 28 product images were dead.** `public/storage` was a junction to a
   directory that no longer exists. Re-pointed at `storage/app/public`.
3. **The admin image page crashed.** `ProductImageController` selected a
   `woo_id` column that does not exist. Removed.
4. **The contact form always 500'd.** `Contact` wrote `inquiry_type` while the
   table has `subject`. Fixed in the model and controller.
5. **ERP receipts lost their item names.** `PosController` wrote
   `sale_items.product_name`, but the column did not exist and was not fillable.
   Added the column and the fillable entry.
6. **Duplicate ERP products.** `ProductSeeder` used `create()` instead of
   `firstOrCreate()`, so it duplicated the demo products on a second run. Made it
   idempotent, and the seeder now ships the real 28-product catalogue instead of
   placeholder demo rows.
7. **Mis-filed ERP products.** Water was under Agriculture and rice under
   Restaurant. Corrected in the seeder and the data.
8. **`activity_logs.user_id` was NOT NULL**, so `log_activity()` crashed for any
   unauthenticated caller such as this API. Made it nullable.

### Found later, by the test suite

See `TESTING.md` for how to run them.

9. **Registration returned HTTP 500.** `RegisteredUserController` wrote
   `$request->can_login` straight through, and an unticked checkbox sends
   nothing, so a NULL hit a NOT NULL column.
10. **`can_login` was not cast to a boolean**, so the model handed `1`/`0` to Vue
    pages that treat it as true/false.
11. **The POS left a database transaction open.** Selling more than the stock
    available returned an error response *without rolling back*, leaving a
    half-written sale and an open transaction on the connection.
12. **Checkout could silently lose an order.** `phone`, `address`, `notes` and
    the per-item `unit` are all optional, and a missing optional key is absent
    from Laravel's validated array rather than null — reading those keys
    directly threw "Undefined array key". A customer who left the notes field
    out got "Failed to create order" and, in the item case, an empty order row
    was left behind. Now null-safe and wrapped in a transaction.
13. **Order confirmation emails were never sent.** `OrderConfirmation` used the
    Breeze placeholder `view: 'view.name'` and discarded the order in its
    constructor. On top of that, the template uses the `mail::message`
    component, so it has to be rendered as `markdown:` — as a plain `view:` the
    `mail::` namespace is never registered and it throws.
14. **`DatabaseSeeder` called `RolesSeeder` and `ProductSeeder` twice**, which is
    what actually produced the duplicate products. Each now runs once.
15. **`CategorySeeder` was not idempotent** — a second `db:seed` duplicated all
    six categories and crashed on the duplicate user emails.
16. **`bootstrap/cache` in the ERP had no `DB_CONNECTION` for tests**, so
    `RefreshDatabase` would have migrated (and wiped) the live database.
    `phpunit.xml` now points tests at in-memory SQLite.
17. **Products could not be saved from the ERP admin at all.** Both
    `ProductForm.vue` and `Edit.vue` omitted the required `unit_cost`, and the
    create form sent `stock` where the controller required `initial_stock`, so
    every save failed validation. The forms now match the controller, and
    `ProductFormContractTest` compares the two sides so a rename on one side
    cannot break the other again.
18. **The ERP had nowhere to put a product image**, which made the admin
    incomplete for a catalogue the shop mirrors. Added `products.image_url`, an
    upload on both product forms, and the image in the catalogue API.

---

## 12. Known limitations

* **Product images are not shared.** ERP products have no images; the frontsite
  keeps its own `image_url`. Products created by a sync appear with the default
  logo until you set an image at `/admin/products/images`.
* **`/admin/products/images` has no authentication.** It is a public upload
  endpoint. Put it behind auth before going live, or set images directly.
* **Sales do not write `inventory_transactions`.** The POS behaves the same way,
  so this keeps the two channels consistent, but it means the stock ledger is not
  a complete audit trail.
* **The catalog sync is one-way** (ERP to frontsite). Editing a name or price on
  the site is overwritten on the next sync; edit it in the ERP instead.
* **The ERP has no link to the leads page in its Vue navigation.** The URL is
  `/online-leads`. Adding a nav item means editing the Vue layout and running
  `npm run build`.
