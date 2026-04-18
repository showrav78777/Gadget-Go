# Gadget & Go (G&G)

PHP-based e-commerce storefront for gadgets (phones, PCs, accessories, cameras, wearables, and more), with an admin area, shopping cart, and **SSLCommerz** payment integration. The public site is branded **Gadget and Go**; the main landing experience lives in `home/home.php`.

## Requirements

- [XAMPP](https://www.apachefriends.org/) (or similar) with **Apache** and **PHP** with **mysqli**
- **MySQL** / MariaDB
- Modern browser

Optional (not required to run the PHP store):

- **Node.js** — only if you use the React scaffold under `src/` (`package.json` uses `react-scripts`).

## Quick start

1. Place the project under your web root (e.g. `htdocs/g&g`).

2. Create a MySQL database (default name in code: `admin_panel1`) and import schema where needed:

   - `admin/admin_product/product.sql` — `categories` and `products`
   - `SSLCommerz-PHP-master/orders.sql` — `orders` (and use related SSLCommerz setup scripts if you enable payments)

3. Edit database credentials in `connection/connection.php`:

   - Host, user, password, and **database name** must match your environment.

4. Open the storefront in the browser (paths in the project assume this base URL):

   - `http://localhost/g&g/home/home.php`

   If your folder name or host differs, search and replace `http://localhost/g&g/` in links or use a single config constant (not present globally today).

## Configuration

### Payments (SSLCommerz)

Settings live in `SSLCommerz-PHP-master/config/config.php`:

- `PROJECT_PATH` — base URL of the site
- `IS_SANDBOX` — `true` for sandbox, `false` for live
- Store ID and store password — use your SSLCommerz credentials; **do not commit live secrets** to public repositories

Success, failure, cancel, and IPN URLs are defined relative to that config.

### Session and auth

- Registration and login: `login_registration/login_registration.php` (passwords verified with `password_verify` against the `users` table).
- Cart: `cart/cart.php` — expects a logged-in user; uses `$_SESSION['cart']` and optional cart cookie.
- Several pages include `includes/session_check.php` from the project root. Ensure that file exists at `includes/session_check.php` next to folders like `cart/` and `home/` (a copy may exist under a nested `g&g/` path in some checkouts).

## Project layout (high level)

| Area | Path |
|------|------|
| DB connection | `connection/connection.php` |
| Home, search, product details | `home/` |
| Cart & order success | `cart/` |
| Checkout UI | `checkout/` |
| User profile & orders | `profile/` |
| Category / product listing pages | `phone/`, `pc/`, `camera/`, `watches/`, `gadgets/`, `headphone&speakers/`, `phone_accessories/` |
| Admin dashboard & catalog | `admin/` |
| SSLCommerz integration | `SSLCommerz-PHP-master/` |
| Global styles | `style.css`, `bootstrap.min.css`, various `navbar.css` / `footer.css` |
| Optional React + Tailwind demo | `src/`, `tailwind.config.js`, `postcss.config.js` |

## Database (from included SQL)

- **`products`** — catalog: brand, model, price, stock, status, `image_url`, optional `category_id` FK to **`categories`**.
- **`orders`** — customer info, amount, status, transaction metadata (SSLCommerz flow).
- **`users`** — used by login/registration (not fully defined in the snippets above; ensure your DB matches what `login_registration.php` expects).
- **`dashboard`** — admin dashboard aggregates (sales, order count, product count).

## Development notes

- Featured products on the home page are loaded with `SELECT * FROM products ORDER BY RAND() LIMIT 30`.
- Hard-coded `http://localhost/g&g/...` URLs appear throughout PHP and HTML; changing deployment host requires updating those or centralizing the base URL.
- `package.json` only defines scripts and Tailwind-related devDependencies; the live storefront pages are primarily server-rendered PHP with Bootstrap and CDN assets (e.g. React UMD on `home/home.php` for optional in-page scripts).

## License

Not specified in this repository; add a `LICENSE` file if you distribute the project.
