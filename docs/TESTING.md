# Running the test suites

Two applications, two runners. Both suites are green and neither touches your
real data.

| App | Framework | Command | Result |
|---|---|---|---|
| `zaheerah` (frontsite) | PHPUnit 11 | `php artisan test` | 164 passing |
| `zaheerah-epr/zaheerah-epr` (ERP) | Pest 3 | `php artisan test` | 155 passing |

```bash
# frontsite
cd zaheerah
php artisan test

# ERP
cd zaheerah-epr/zaheerah-epr
php artisan test

# a single file
php artisan test tests/Feature/Api/OrderIntakeApiTest.php

# a single test (Pest)
php artisan test --filter="does not deduct the stock twice"
```

---

## Tests never touch your data

Both `phpunit.xml` files point the suite at **in-memory SQLite**:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

This matters more than it sounds. The ERP's `phpunit.xml` had those two lines
**commented out** while every Feature test uses `RefreshDatabase` — so running
the suite would have migrated straight over `database/database.sqlite` and wiped
your catalogue, sales and staff accounts. Fixed before any test ran.

`RefreshDatabase` gives each test a clean schema, so tests are independent and
order does not matter.

---

## ERP setup note

Pest was listed in `composer.json` but **not installed** (no `vendor/bin/pest`,
no `vendor/bin/phpunit`), so none of the nine existing test files could run at
all. If you ever rebuild `vendor`, install dev dependencies:

```bash
cd zaheerah-epr/zaheerah-epr
composer install          # NOT --no-dev
php artisan test
```

---

## What is covered

### ERP — the storefront API (`tests/Feature/Api/`)

| File | Covers |
|---|---|
| `StorefrontAuthTest.php` | The shared secret: missing key, wrong key, correct key, fail-closed when unconfigured, every endpoint guarded, and that a logged-in session does **not** grant API access. |
| `CatalogApiTest.php` | Response shape, category names, no cost prices leaked, `updated_since` filtering, upsert create/update-by-name, idempotent opening stock, validation. |
| `OrderIntakeApiTest.php` | Sale creation, `channel = web`, customer details, `product_name` on line items, stock deduction, matching by id/name (case-insensitive), server-side totals, **idempotency** (no double sale, no double deduction), unknown-product auto-creation, stock clamped at zero, validation, activity logging. |
| `LeadIntakeApiTest.php` | Quote and contact intake, cart payload, dedupe by external ref, listing and filtering. |

### ERP — regressions and existing behaviour

| File | Covers |
|---|---|
| `SeederIdempotencyTest.php` | `db:seed` run twice duplicates nothing (products, categories, users, roles); the seeded catalogue is the real 28 products with a price, under the shop category, **every image file present both in the repository and on the public disk**, and an image chosen in the admin is not overwritten by reseeding. |
| `PosSaleTest.php` | In-store sales: permission enforcement, `product_name` recorded, stock deducted, oversell refused **with no partial sale left behind**, activity log accuracy. |
| `ProductImageTest.php` | Products are created in the admin: create and edit persist, image upload stored on the public disk, replaced on edit, kept when no new file is sent, non-images rejected, and the catalogue API hands the shop an absolute image URL. Also covers **importing** an image: the ERP downloads and stores its own copy, refuses non-images, keeps the link and warns when a download fails, and never re-downloads a file it already owns. |
| `ProductFormContractTest.php` | Compares the Vue product forms against `productController` and fails if a required field is missing from one side. This is the test that catches form/controller drift. |
| `OnlineLeadPageTest.php` | The staff leads page: auth-gated, shows quotes and contacts, filters, status changes, validation. |
| `ActivityLogTest.php` | `log_activity()` works with no authenticated user (the NOT NULL regression). |
| `Auth/RegistrationTest.php` | Registration, required phone, duplicate email, password hashing, and the unticked-`can_login` crash. |

### Frontsite — the ERP integration

| File | Covers |
|---|---|
| `tests/Unit/ErpClientTest.php` | Endpoint URLs, the `X-Storefront-Key` header, error handling, retries only on server faults (a 401/422 is not retried). |
| `tests/Feature/ErpOrderSyncTest.php` | Order payload shape, ERP product id resolution, skip-if-already-pushed, failure recording, retry, warnings kept on success. |
| `tests/Feature/ErpLeadSyncTest.php` | Lead forwarding never throws, even when the ERP is down or the key is rejected. |
| `tests/Feature/SyncErpCatalogCommandTest.php` | Create/update/skip, **marketing fields are never overwritten**, images taken from the ERP but never blanked when the ERP has none, legacy products untouched, `--dry-run`, `--prune`, clean failure. |
| `tests/Feature/PublishProductsToErpCommandTest.php` | Dry run by default, `--force`, `--stock`, `--category`, image URL sent absolute, already-linked skipping, per-product failures. |
| `tests/Feature/PushPendingOrdersCommandTest.php` | Retries only paid-and-unrecorded orders, honours `--limit`, clears stale errors. |

### Frontsite — shop and forms

| File | Covers |
|---|---|
| `CheckoutCallbackTest.php` | The whole payment seam: Paystack verify → order paid → ERP sale recorded, confirmation email, ERP outage still confirming the customer, failed payments, missing/unknown references, optional-field checkout. |
| `MailableTest.php` | Every mailable **actually renders**, with its content. |
| `ContactSubmissionTest.php` | The `inquiry_type` → `subject` regression, consent, ERP forwarding, validation. |
| `QuoteRequestTest.php` | Quote storage, ERP lead, admin and customer emails, validation. |
| `ShopControllerTest.php` | Active products only, filter buttons derived from real categories, labels, live availability, cart fields. |
| `ProductModelTest.php` | Slug generation, `isInStock()` / `availability()` for ERP and legacy rows. |
| `ProductImageAdminTest.php` | The admin image page renders (the `woo_id` regression). |

---

## Tips and gotchas

* **`Http::fake()` merges stubs; it does not reset them.** A second call inside
  one test leaves the first stub active and the first match wins. Use a single
  stateful closure instead — `ErpOrderSyncTest` and `ErpClientTest` show both
  patterns.
* **`Mail::fake()` never renders a mailable.** It only proves one was handed to
  the mailer, which is exactly how a broken confirmation email stayed hidden.
  `MailableTest` calls `->render()` for real.
* **Retry behaviour is intentional.** `ErpClient` retries server errors (5xx)
  once but never a 4xx, because a rejected key or a validation error fails
  identically on a second attempt.
* **The ERP suite needs its test DB env vars.** If you add a test that hits the
  database and you see it talking to `database.sqlite`, check `phpunit.xml`
  first.
