#!/usr/bin/env bash
# Sync Stripe test keys from .env into WooCommerce settings.
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

WP_PATH="wp"

if command -v lando >/dev/null 2>&1; then
  WP="lando wp"
else
  WP="wp"
fi

if [[ ! -f "$ROOT_DIR/.env" ]]; then
  echo "Error: .env file not found. Copy .env.example to .env and add your Stripe test keys."
  exit 1
fi

set -a
# shellcheck disable=SC1091
source "$ROOT_DIR/.env"
set +a

STRIPE_PUB="${STRIPE_TEST_PUBLISHABLE_KEY:-}"
STRIPE_SEC="${STRIPE_TEST_SECRET_KEY:-}"

if [[ -z "$STRIPE_PUB" || -z "$STRIPE_SEC" ]]; then
  echo "Error: STRIPE_TEST_PUBLISHABLE_KEY and STRIPE_TEST_SECRET_KEY must be set in .env"
  exit 1
fi

if [[ "$STRIPE_PUB" == *"your_publishable_key_here"* || "$STRIPE_SEC" == *"your_secret_key_here"* ]]; then
  echo "Error: Replace the placeholder Stripe keys in .env with your real test keys from https://dashboard.stripe.com/test/apikeys"
  exit 1
fi

echo "==> Syncing Stripe test keys from .env to WooCommerce"

$WP eval "
\$settings = get_option( 'woocommerce_stripe_settings', array() );
if ( ! is_array( \$settings ) ) {
  \$settings = array();
}
\$settings['enabled']              = 'yes';
\$settings['testmode']             = 'yes';
\$settings['test_publishable_key'] = '$STRIPE_PUB';
\$settings['test_secret_key']      = '$STRIPE_SEC';
\$settings['title']                = \$settings['title'] ?? 'Credit / Debit Card';
\$settings['description']          = \$settings['description'] ?? 'Pay securely with your credit or debit card via Stripe.';
\$settings['capture']              = \$settings['capture'] ?? 'yes';
update_option( 'woocommerce_stripe_settings', \$settings );
echo 'Stripe keys updated successfully.';
" --path="$WP_PATH"

echo "Done. Test checkout with card: 4242 4242 4242 4242"
