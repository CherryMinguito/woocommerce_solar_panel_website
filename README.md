# Sunrooflighting

A WooCommerce solar installation website for **Sunrooflighting**, featuring installation packages, a bill-upload savings calculator, quote requests, and credit card + financing checkout.

## Requirements

- [Lando](https://docs.lando.dev/getting-started/installation.html) 3.x+
- Docker (Lando uses Docker under the hood)

## Quick Start

```bash
# 1. Copy environment file and add your API keys
cp .env.example .env

# 2. Start Lando (installs Tesseract OCR for bill reading)
lando start

# 3. Bootstrap WordPress, WooCommerce, theme, and demo data
lando bootstrap
# or: bash setup/setup.sh
```

Site URL: **https://sunrooflighting.lndo.site**

Admin: credentials from `.env` (default `admin` / `admin`)

## What Gets Installed

- WordPress (latest)
- WooCommerce
- Storefront parent theme + **sunrooflighting** child theme
- WooCommerce Stripe Payment Gateway (test mode)
- Custom Solar Financing payment gateway (provider-agnostic scaffold)
- Solar categories, installation packages, and equipment products
- Pages: Savings Calculator, Get a Quote, Financing

## Features

### Solar Installation Packages
Quote-only packages (Residential & Commercial) displayed on the homepage and shop. Customers request a quote instead of checking out directly.

### Savings Calculator (`/calculator/`)
Upload a utility bill (PDF/JPG/PNG) — Tesseract OCR extracts kWh and bill amount. Manual entry fallback. Returns system size, estimated cost, annual savings, and payback period.

### Quote Request Form (`/quote/`)
Contact form with package selection and optional bill upload. Submissions stored as `quote_request` posts in WP Admin and emailed to the site admin.

### Payments
- **Credit card**: Stripe test mode (configure keys in `.env`)
- **Solar financing**: Custom gateway at checkout. Set `FINANCING_API_URL` and `FINANCING_API_KEY` in `.env` when your lender API is ready; otherwise applications are emailed to admin.

## Stripe Test Checkout

1. Add your Stripe **test** keys to `.env`:
   - `STRIPE_TEST_PUBLISHABLE_KEY`
   - `STRIPE_TEST_SECRET_KEY`
2. Re-run setup or configure in **WooCommerce → Settings → Payments → Stripe**.
3. Or sync keys from `.env` anytime: `lando stripe-sync`
4. Use test card: `4242 4242 4242 4242`, any future expiry, any CVC, any ZIP.

## Useful Commands

```bash
lando start          # Start the environment
lando stop           # Stop the environment
lando wp ...         # Run WP-CLI commands
lando bootstrap      # Full bootstrap (install WP, WooCommerce, seed data)
lando seed           # Re-seed demo products/categories
lando stripe-sync    # Sync Stripe keys from .env
lando info           # Show URLs and connection info
```

## Project Structure

```
.
├── .lando.yml                          # Lando WordPress recipe (+ Tesseract OCR)
├── setup/
│   ├── setup.sh                        # Bootstrap script
│   ├── seed.php                        # Solar categories & products
│   └── sync-stripe.sh                  # Stripe key sync
└── wp-content/themes/sunrooflighting/
    ├── front-page.php                  # Solar homepage
    ├── page-calculator.php             # Savings calculator
    ├── page-quote.php                  # Quote request form
    ├── page-financing.php              # Financing info
    ├── inc/                            # OCR, calculator, quote, financing
    ├── template-parts/                 # Header, footer, home sections
    └── woocommerce/                    # Product list overrides
```

## Category Structure

- **Installation Packages** → Residential, Commercial
- **Solar Panels**, **Inverters**, **Batteries & Storage**, **Accessories**

## Development

The child theme lives in `wp-content/themes/sunrooflighting/` and is synced into the Lando webroot on start. Edit theme files and refresh the browser.

## Going Live

1. Deploy WordPress + theme to hosting
2. Switch Stripe to live keys in WooCommerce settings
3. Configure financing provider API in `.env`
4. Replace demo products with real catalog (CSV import supported)
5. Set `QUOTE_NOTIFICATION_EMAIL` in `.env` for quote/financing notifications
