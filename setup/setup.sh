#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

WP_PATH="wp"

if command -v lando >/dev/null 2>&1; then
  WP="lando wp"
else
  WP="wp"
fi

if [[ -f "$ROOT_DIR/.env" ]]; then
  set -a
  # shellcheck disable=SC1091
  source "$ROOT_DIR/.env"
  set +a
fi

WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@sunrooflighting.test}"
SITE_URL="https://sunrooflighting.lndo.site"
SITE_TITLE="Sunrooflighting"

echo "==> Syncing child theme into webroot"
mkdir -p "$WP_PATH/wp-content/themes"
cp -r "$ROOT_DIR/wp-content/themes/sunrooflighting" "$WP_PATH/wp-content/themes/"

if [[ ! -f "$WP_PATH/wp-config.php" ]]; then
  echo "==> Downloading WordPress core"
  $WP core download --path="$WP_PATH" --force

  echo "==> Creating wp-config.php"
  $WP config create \
    --path="$WP_PATH" \
    --dbname=wordpress \
    --dbuser=wordpress \
    --dbpass=wordpress \
    --dbhost=database \
    --skip-check
fi

if ! $WP core is-installed --path="$WP_PATH" 2>/dev/null; then
  echo "==> Installing WordPress"
  $WP core install \
    --path="$WP_PATH" \
    --url="$SITE_URL" \
    --title="$SITE_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
else
  echo "==> WordPress already installed, skipping core install"
fi

echo "==> Installing plugins and themes"
$WP plugin install woocommerce --activate --path="$WP_PATH" --force
$WP plugin install woocommerce-gateway-stripe --activate --path="$WP_PATH" --force
$WP theme install storefront --path="$WP_PATH" --force
$WP theme activate sunrooflighting --path="$WP_PATH"

echo "==> Configuring WooCommerce"
$WP option update woocommerce_store_address "500 Solar Way" --path="$WP_PATH"
$WP option update woocommerce_store_city "Phoenix" --path="$WP_PATH"
$WP option update woocommerce_default_country "US:AZ" --path="$WP_PATH"
$WP option update woocommerce_store_postcode "85001" --path="$WP_PATH"
$WP option update woocommerce_currency "USD" --path="$WP_PATH"
$WP option update woocommerce_onboarding_profile '{"skipped":true}' --format=json --path="$WP_PATH"
$WP option update woocommerce_coming_soon no --path="$WP_PATH"
$WP option update woocommerce_store_pages_only no --path="$WP_PATH"
$WP wc tool run install_pages --user=1 --path="$WP_PATH" 2>/dev/null || true

echo "==> Setting permalinks and homepage"
$WP rewrite structure '/%postname%/' --path="$WP_PATH"
$WP rewrite flush --path="$WP_PATH"

if [[ ! -f "$WP_PATH/.htaccess" ]]; then
  cat > "$WP_PATH/.htaccess" <<'HTACCESS'
# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
# END WordPress
HTACCESS
fi

create_page() {
  local title="$1"
  local slug="$2"
  local template="$3"
  local excerpt="${4:-}"
  local existing
  existing=$($WP post list --post_type=page --name="$slug" --field=ID --path="$WP_PATH" 2>/dev/null || echo "")
  if [[ -z "$existing" ]]; then
    existing=$($WP post create --post_type=page --post_title="$title" --post_name="$slug" --post_status=publish --post_excerpt="$excerpt" --porcelain --path="$WP_PATH")
  elif [[ -n "$excerpt" ]]; then
    $WP post update "$existing" --post_excerpt="$excerpt" --path="$WP_PATH"
  fi
  if [[ -n "$template" ]]; then
    $WP post meta update "$existing" _wp_page_template "$template" --path="$WP_PATH"
  fi
  echo "$existing"
}

echo "==> Setting site tagline and SEO defaults"
$WP option update blogdescription "Professional solar panel installation, free quotes, and flexible financing in Arizona." --path="$WP_PATH"

FRONT_PAGE_ID=$(create_page "Home" "home" "" "Sunrooflighting — professional residential and commercial solar installation, savings calculator, and free quotes.")
$WP option update show_on_front page --path="$WP_PATH"
$WP option update page_on_front "$FRONT_PAGE_ID" --path="$WP_PATH"

echo "==> Downloading default Open Graph image"
mkdir -p "$ROOT_DIR/wp-content/themes/sunrooflighting/assets/images"
if [[ ! -f "$ROOT_DIR/wp-content/themes/sunrooflighting/assets/images/og-default.jpg" ]]; then
  curl -sL "https://picsum.photos/seed/sunrooflighting-og/1200/630" -o "$ROOT_DIR/wp-content/themes/sunrooflighting/assets/images/og-default.jpg" || true
fi
cp -r "$ROOT_DIR/wp-content/themes/sunrooflighting" "$WP_PATH/wp-content/themes/"

create_page "Savings Calculator" "calculator" "page-calculator.php" "Upload your utility bill and get an instant solar savings estimate with system size, cost, and payback period." >/dev/null
create_page "Get a Quote" "quote" "page-quote.php" "Request a free personalized solar installation quote from Sunrooflighting. Response within 1 business day." >/dev/null
create_page "Financing" "financing" "page-financing.php" "Flexible solar financing and credit card payment options for your solar installation." >/dev/null

echo "==> Configuring Stripe (test mode)"
STRIPE_PUB="${STRIPE_TEST_PUBLISHABLE_KEY:-}"
STRIPE_SEC="${STRIPE_TEST_SECRET_KEY:-}"

STRIPE_SETTINGS=$(cat <<EOF
{
  "enabled": "yes",
  "title": "Credit / Debit Card",
  "description": "Pay securely with your credit or debit card via Stripe.",
  "testmode": "yes",
  "test_publishable_key": "$STRIPE_PUB",
  "test_secret_key": "$STRIPE_SEC",
  "capture": "yes",
  "payment_request": "yes",
  "saved_cards": "yes"
}
EOF
)
$WP option update woocommerce_stripe_settings "$STRIPE_SETTINGS" --format=json --path="$WP_PATH"

echo "==> Enabling financing payment gateway"
$WP option update woocommerce_jcs_financing_settings '{"enabled":"yes","title":"Solar Financing","description":"Apply for monthly financing on your solar installation."}' --format=json --path="$WP_PATH"

echo "==> Registering filter widgets"
$WP eval 'if ( function_exists( "jcs_register_default_widgets" ) ) { delete_option( "jcs_widgets_registered" ); jcs_register_default_widgets(); }' --path="$WP_PATH"

echo "==> Seeding categories and demo products"
$WP eval-file setup/seed.php --path="$WP_PATH"

echo ""
echo "============================================"
echo "  Sunrooflighting is ready!"
echo "  Site:  $SITE_URL"
echo "  Admin: $SITE_URL/wp-admin"
echo "  User:  $WP_ADMIN_USER"
echo "============================================"
