<?php
/**
 * WooCommerce wrapper template.
 *
 * @package Jed_Construction_Supply
 */

get_header();
?>

<main id="main-content" class="jcs-woo-main">
	<?php
	if ( is_shop() || is_product_taxonomy() ) {
		wc_get_template( 'archive-product.php' );
	} else {
		woocommerce_content();
	}
	?>
</main>

<?php
get_footer();
