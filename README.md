# Invoiz — Desktop Website

A professional desktop e-commerce website built with:

- **Frontend:** Laravel Blade templates (`backend/resources/views/`) with **inline CSS** and **vanilla JS** (no Flutter/Dart, no mobile app tools)
- **Backend:** Laravel 13 / PHP 8.5 (`backend/`)
- **Database:** MySQL / MariaDB (`invoizdb` — shared between `invoiz` and `Invoiz-main` via XAMPP)

**Roles:** Guest (browse), Buyer (shop, cart, checkout, orders, chat) — Buyer accounts are **auto-approved** on register (no pending).

> Desktop only — responsive website with sidebar + burger hide, topbar 48px, hero 180px, 5/4/3/2/1 grid, hover `translateY(-4px)`, product color with name, cart badge `1..99+`, COD only.

---

## 1. Requirements

| Tool     | Version | Notes |
|----------|---------|-------|
| PHP      | 8.5     | `php --version` (OneDrive\Desktop\php\php\php.exe or XAMPP) |
| Composer | 2.x     | `composer --version` |
| MySQL    | MariaDB 10.4+ / MySQL 8.0 | XAMPP `mysqld` on 3306, `DB_PASSWORD=""` |
| Node     | 24.x    | `node --version` for `npm start` (only runs `php artisan serve`) |

Make sure **XAMPP MySQL is running** (3306) with `invoizdb` (135 products, 20 categories).

---

## 2. Project Structure (PHP/Laravel only)

```
Invoiz-main/
├── backend/                  # Laravel — PHP/Laravel only
│   ├── app/Http/Controllers/Web/  # HomeController, AuthController (Blade)
│   ├── app/Http/Controllers/Api/   # API for legacy
│   ├── app/Models/                # User, Product, Category, Order, Cart
│   ├── resources/views/
│   │   ├── layouts/website.blade.php  # Desktop shell: topbar 48 + sidebar 220 + nav 34 + footer (inline CSS)
│   │   ├── layouts/auth.blade.php     # Auth without sidebar (only form)
│   │   ├── home.blade.php             # Hero 180 + categories 2-line wrap + grid 5/4/3/2/1 + color with name
│   │   ├── product.blade.php          # Gallery 420 with object-fit:contain (whole picture visible)
│   │   ├── cart.blade.php             # Colored thumb + Qty Buy/Remove, Buy All (same store check removed)
│   │   ├── checkout.blade.php         # COD only
│   │   ├── orders.blade.php           # My Orders with product picture
│   │   ├── profile.blade.php          # Full-screen editable
│   │   ├── auth/login.blade.php / register.blade.php # All required fields + Google button
│   │   └── chat.blade.php / messages.blade.php / notifications.blade.php
│   ├── routes/web.php                # Blade: /, /products, /product/{id}, /cart, /checkout, /orders, /profile, /messages
│   ├── routes/api.php                # API: /api/categories, /api/products, /api/cart, /api/orders/checkout
│   ├── public/images/logo.png        # INVOIZ ONLINE SHOPPING logo (orange O)
│   └── storage/app/public/products/  # 50+ product images (exact name)
│   ├── database/invoizdb.sql
│   └── .env (DB_DATABASE=invoizdb, DB_USERNAME=root, DB_PASSWORD="")
├── package.json              # start/dev/serve = php backend/artisan serve --host=127.0.0.1 --port=8000
└── README.md
```

**No `frontend/` Flutter/Dart** — removed (`frontend/.dart_tool, android, lib Dart, build` deleted). No mobile app tools.

---

## 3. Database Setup

Both `Desktop\invoiz\backend\.env` and `Invoiz-main\backend\.env` share `invoizdb` via your XAMPP MySQL (`DB_PASSWORD=""`):

```bash
# XAMPP MySQL must be running (3306)
mysql -u root -e "SHOW DATABASES;" # should show invoizdb
# If needed, import (already has 135 products, 20 categories, 3 users)
mysql -u root < database/invoizdb.sql
```

Default DB: `root` / no password (XAMPP). If `MySQL80` is running on 3306, stop it: `sc stop MySQL80` then start XAMPP `mysqld --defaults-file=C:\xampp\mysql\bin\my.ini`.

---

## 4. Backend (Laravel — PHP/Laravel only, Blade inline CSS + vanilla JS)

```bash
cd backend
composer install
php artisan storage:link
php artisan migrate:status # no pending
php artisan serve --host=127.0.0.1 --port=8000
# or from root
npm start  # = php backend/artisan serve
```

Website runs at `http://127.0.0.1:8000` — desktop Blade (no Dart).

**Categories:** 20 (Appliances 11, Baby Clothes 7, Beauty 12, Books 1, Dresses 11, etc. — Electronic/Gadgets removed, Toys merged, Beauty+Makeup merged to Beauty, Health separate)

**Products:** 135 with color variant (3 colors) + exact images for 44+ (e.g., `air_purifier.png`, `kettle.png` for Appliances)

**Auth:** `POST /register` (first_name, last_name, birthday, sex, phone, email, password, address_line) → auto `approved` + `Auth::login` + `Cart` + redirect `/` — `POST /login` (juan@test.com / password123, seller@invoiz.test / password, cmiavenus@gmail.com / password123 Mia). `Continue with Google` button shows message (needs `GOOGLE_CLIENT_ID` + `laravel/socialite`).

**Buyer:** cart `session('cart')` with badge `1..99+` (valid products only, cleaned), `Buy` single → checkout single, `Buy All` any store, `COD` only, orders appear in `My Orders` with picture.

---

## 5. Frontend (Blade — inline CSS, vanilla JS)

No Flutter. Blade uses `layouts/website.blade.php` (`:root` `--primary #16697A`, `topbar 48`, `sidebar 220` collapsible via `☰` burger `onclick="sidebar.classList.toggle('collapsed')"` vanilla JS).

- **Home:** hero `180px` (logo 80px in `Invoiz — Curated for you` + `Welcome Save 15 COD` + `Free COD` below logo), categories `flex-wrap 2-line` (Appliances, Books, Dress, etc.), grid `5/4/3/2/1` responsive + hover lift
- **Product:** `420px` `object-fit:contain` whole picture visible + `Add to Cart` / `Buy Now` + `Message Seller`
- **Cart:** empty shows `Your cart is empty` only (no header), with items shows `72×72` color/initial or image + `Qty −/+` + `Buy` on right, `Buy All`
- **Checkout:** `COD` only (GCash/PayMaya removed)
- **Orders:** `Order #` with `48×48` product picture + name
- **Profile:** full-screen editable (click default profile `Mia` beside name → dropdown → `View / Edit Profile`)
- **Messages:** `Messages` in sidebar → `Invoiz Store` list like Shopee

---

## 6. Test Accounts

| Role | E-mail | Password | Status |
|------|--------|----------|--------|
| Buyer Mia | `cmiavenus@gmail.com` | `password123` | approved |
| Buyer Juan | `juan@test.com` | `password123` | approved |
| Seller | `seller@invoiz.test` | `password` | approved |

Register creates approved buyer instantly (no pending).

---

## 7. Payment

Only **Cash on Delivery (COD)** — checkout shows `Cash on Delivery (COD) — only payment method`.

---

## 8. Troubleshooting

- **`Access denied for user 'root'`** — ensure XAMPP `mysqld` on `3306` (not `MySQL80` on `33060`), `DB_PASSWORD=""` in both `.env`.
- **Port 8000 in use** — `netstat -ano | findstr :8000` + `taskkill /PID`.
- **Cart badge 2 but empty** — was deleted products in session, fixed to count only valid `Product::whereIn` + clean `session('cart')`.
- **`npm start` ENOENT** — `package.json` now exists at root, `npm start` = `php artisan serve`.

---

*No Flutter/Dart, no mobile app tools — pure PHP/Laravel Blade (inline CSS, vanilla JS) desktop website.*
