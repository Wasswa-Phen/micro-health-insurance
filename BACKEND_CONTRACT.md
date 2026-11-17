# Backend Contract — Micro Health Insurance System (Kampala)

This document is the single source of truth for wiring frontend forms to the PHP backend.
**Every `action` path and `name` attribute below must be used exactly as written.**

- Stack: PHP 8 · MySQL/MariaDB (PDO) · HTML5 · CSS3 · Vanilla JS · XAMPP
- Base URL (local): `http://localhost/micro-health-insurance`
- All paths below are relative to the project root. In PHP templates you may prefix them with `<?= BASE_URL ?>`.

> [!IMPORTANT]
> **Database port:** `config/db.php` connects to MySQL on **port 3307** (this developer's local XAMPP).
> On a **standard XAMPP** install MySQL listens on **3306** — change `define('DB_PORT', '3307');` to `'3306'` in `config/db.php`.

---

## 1. Global conventions

| Item | Rule |
|---|---|
| Bootstrap | Every PHP page starts with `require_once __DIR__ . '/../config/db.php';` (gives `$pdo`, session, helpers) |
| CSRF | **Every POST form** must include `<?= csrfField() ?>` → renders `<input type="hidden" name="csrf_token">`. Handlers call `verifyCsrf()`. GET forms do not need it. |
| Phone format | Ugandan local format `07XXXXXXXX` (10 digits). HTML: `pattern="07[0-9]{8}" maxlength="10" inputmode="numeric"` |
| Money | Whole UGX, no decimals in inputs: `type="number" min="..." step="100"`. Display with `formatUGX($amount)` → `UGX 10,500` |
| Output escaping | Echo any user/DB value with `e($value)` |
| Feedback | Handlers redirect back (Post/Redirect/Get) and set a flash. Render it with: `<?php if ($f = getFlash()): ?><div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endif; ?>` — `type` is `success`, `error` or `info` |
| Sticky values | On validation error, handlers store previous input in `$_SESSION['old']` (array keyed by field `name`); frontend may repopulate with `e($_SESSION['old']['field'] ?? '')` |
| Session keys (after login) | `user_id`, `role` (`admin`/`attendant`/`client`), `full_name`, `phone`, `pharmacy_id` (attendant & client; `NULL` for admin) |
| Access control | Protected pages call `requireRole('client')`, `requireRole('attendant')` or `requireRole('admin')` |

---

## 2. Login

| | |
|---|---|
| Page (form) | `auth/login.php` (GET renders form) |
| Form | `<form method="POST" action="auth/login.php">` |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `phone` | `tel` | ✔ | `07XXXXXXXX` |
| `password` | `password` | ✔ | min 6 chars |
| `csrf_token` | hidden | ✔ | `<?= csrfField() ?>` |

**Success redirect by role:**
- `admin` → `admin/dashboard.php`
- `attendant` → `attendant/dashboard.php`
- `client` → `client/dashboard.php`

**Failure:** redirect to `auth/login.php` with flash `error` ("Invalid phone number or password." / "Account suspended.").

**Logout:** link `<a href="auth/logout.php">` (GET) → destroys session → `auth/login.php`.

---

## 3. Client Registration

| | |
|---|---|
| Page (form) | `auth/register.php` |
| Form | `<form method="POST" action="auth/register.php">` |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `full_name` | `text` | ✔ | 3–120 chars |
| `phone` | `tel` | ✔ | `07XXXXXXXX`, must be unique |
| `nin` | `text` | ✖ | Uganda NIN, 14 alphanumeric chars, unique if given. HTML: `pattern="[A-Za-z0-9]{14}" maxlength="14"` |
| `pharmacy_id` | `select` | ✔ | Home pharmacy. Options = active rows of `pharmacies` (`value` = `pharmacy_id`, label = `name — location`) |
| `password` | `password` | ✔ | min 6 chars |
| `confirm_password` | `password` | ✔ | must equal `password` |
| `csrf_token` | hidden | ✔ | `<?= csrfField() ?>` |

Backend creates the `users` row (`role='client'`) **and** a `policies_wallets` row (`balance = 0`, policy number `MHI-KLA-YYYY-#####`) in one transaction.

**Success:** redirect to `auth/login.php` with flash `success`. **Failure:** back to `auth/register.php` with flash `error` + `$_SESSION['old']`.

---

## 4. Micro-Deposit (Client contribution)

| | |
|---|---|
| Access | `requireRole('client')` |
| Page (form) | `client/deposit.php` |
| Form | `<form method="POST" action="client/deposit.php">` |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `amount` | `number` | ✔ | UGX **1,000 – 500,000**, `step="100"` |
| `payment_method` | `radio` or `select` | ✔ | Exactly `MTN_MOMO` or `AIRTEL_MONEY` |
| `payer_phone` | `tel` | ✔ | `07XXXXXXXX` — mobile money number being charged (defaults to client's phone; may be a relative's) |
| `csrf_token` | hidden | ✔ | `<?= csrfField() ?>` |

Example radio markup:
```html
<input type="radio" name="payment_method" value="MTN_MOMO" id="pm-mtn" required> <label for="pm-mtn">MTN MoMo</label>
<input type="radio" name="payment_method" value="AIRTEL_MONEY" id="pm-airtel"> <label for="pm-airtel">Airtel Money</label>
```

Backend (simulated mobile-money) inserts a `contributions` row (`status='completed'`, generated `transaction_ref`) and credits `policies_wallets.balance` in one transaction. The `wallet_id` is taken from the session user — **never** from the form.

**Success/Failure:** redirect to `client/deposit.php` (or `client/dashboard.php`) with flash.

---

## 5. Attendant Client Lookup

| | |
|---|---|
| Access | `requireRole('attendant')` |
| Page | `attendant/lookup.php` |
| Form | `<form method="GET" action="attendant/lookup.php">` (no CSRF — read only) |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `phone` | `tel` | ✔ | `07XXXXXXXX` of the client |

Result URL example: `attendant/lookup.php?phone=0701555111`

The page then renders (variables available to the template):

| Variable | Contents |
|---|---|
| `$client` | `null` if not found, else array: `user_id`, `full_name`, `phone`, `nin`, `wallet_id`, `policy_number`, `plan_name`, `balance`, `wallet_status` |
| `$recentClaims` | last 5 claims for the wallet (`created_at`, `prescription_ref`, `amount`, `status`, pharmacy `name`) |

If `$client` is found and `wallet_status === 'active'`, render the **Prescription Claim** form (section 6) on the same page.

---

## 6. Prescription Claim Processing

| | |
|---|---|
| Access | `requireRole('attendant')` |
| Form location | Inside `attendant/lookup.php` (after a successful lookup) |
| Form | `<form method="POST" action="attendant/process_claim.php">` |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `wallet_id` | hidden | ✔ | `<?= e($client['wallet_id']) ?>` |
| `client_phone` | hidden | ✔ | `<?= e($client['phone']) ?>` (used to redirect back to the lookup) |
| `prescription_ref` | `text` | ✔ | max 50 chars, e.g. `RX-KCCA-26-004517` |
| `prescriber_name` | `text` | ✖ | max 120 chars (doctor / health centre) |
| `diagnosis` | `text` | ✖ | max 255 chars |
| `medicines` | `textarea` | ✔ | medicines + quantities dispensed |
| `amount` | `number` | ✔ | UGX ≥ 500, `step="100"`, must be ≤ client's wallet balance |
| `csrf_token` | hidden | ✔ | `<?= csrfField() ?>` |

Security: `pharmacy_id` and `attendant_id` are taken from the **session**, never from the form.
Backend locks the wallet row (`SELECT ... FOR UPDATE`), checks the balance, inserts into `claims` and debits `policies_wallets.balance` in one transaction. Insufficient balance → flash `error`, no claim stored.

**Redirect:** `attendant/lookup.php?phone={client_phone}` with flash `success` ("Claim approved: UGX 7,500 deducted.") or `error`.

---

## 7. Admin — Add Pharmacy

| | |
|---|---|
| Access | `requireRole('admin')` |
| Page (form) | `admin/pharmacies.php` (list + add form) |
| Form | `<form method="POST" action="admin/add_pharmacy.php">` |

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `name` | `text` | ✔ | 3–120 chars, unique |
| `license_no` | `text` | ✔ | NDA licence, max 50 chars, unique (e.g. `NDA/RP/2026/0412`) |
| `location` | `select` or `text` | ✔ | Division: `Kampala Central Division`, `Kawempe Division`, `Makindye Division`, `Nakawa Division`, `Rubaga Division` |
| `address` | `text` | ✔ | max 255 chars |
| `phone` | `tel` | ✔ | 10 digits, `pattern="0[0-9]{9}"` (landline `0414…` or mobile `07…`) |
| `csrf_token` | hidden | ✔ | `<?= csrfField() ?>` |

**Redirect:** `admin/pharmacies.php` with flash `success` or `error`.

---

## 8. Quick reference

| Feature | Method | Action | Fields |
|---|---|---|---|
| Login | POST | `auth/login.php` | `phone`, `password`, `csrf_token` |
| Logout | GET | `auth/logout.php` | — |
| Client Registration | POST | `auth/register.php` | `full_name`, `phone`, `nin`, `pharmacy_id`, `password`, `confirm_password`, `csrf_token` |
| Micro-Deposit | POST | `client/deposit.php` | `amount`, `payment_method`, `payer_phone`, `csrf_token` |
| Client Lookup | GET | `attendant/lookup.php` | `phone` |
| Prescription Claim | POST | `attendant/process_claim.php` | `wallet_id`, `client_phone`, `prescription_ref`, `prescriber_name`, `diagnosis`, `medicines`, `amount`, `csrf_token` |
| Add Pharmacy | POST | `admin/add_pharmacy.php` | `name`, `license_no`, `location`, `address`, `phone`, `csrf_token` |

## 9. Demo accounts (password for all: `password`)

| Role | Phone | Name | Notes |
|---|---|---|---|
| Admin | `0700000001` | System Administrator | — |
| Attendant | `0772100101` | Nakato Sarah | Kisenyi Care Pharmacy |
| Client | `0701555111` | Mukasa John Bosco | Policy `MHI-KLA-2026-00001`, balance UGX 10,500 |
| Client | `0782555222` | Namuli Grace | Policy `MHI-KLA-2026-00002`, balance UGX 15,000 |

