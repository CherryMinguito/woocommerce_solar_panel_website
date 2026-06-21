<?php
/**
 * Product card override.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$is_quote_only = jcs_is_quote_only_product( $product );
?>
<li <?php wc_product_class( 'jcs-product-card', $product ); ?>>
	<div class="jcs-product-card-inner">
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="jcs-product-image">
			<?php echo $product->get_image( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>

		<div class="jcs-product-info">
			<h2 class="jcs-product-title">
				<a href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo esc_html( $product->get_name() ); ?>
				</a>
			</h2>

			<?php if ( $product->get_average_rating() > 0 ) : ?>
				<div class="jcs-product-rating">
					<?php echo wc_get_rating_html( $product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="jcs-review-count">(<?php echo esc_html( $product->get_review_count() ); ?>)</span>
				</div>
			<?php endif; ?>

			<div class="jcs-product-price">
				<?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php if ( $is_quote_only ) : ?>
					<small class="jcs-price-note"><?php esc_html_e( 'starting at', 'sunrooflighting' ); ?></small>
				<?php endif; ?>
			</div>

			<div class="jcs-product-badges">
				<?php if ( $is_quote_only ) : ?>
					<span class="jcs-badge jcs-badge--quote"><?php esc_html_e( 'Quote Required', 'sunrooflighting' ); ?></span>
				<?php elseif ( $product->is_in_stock() ) : ?>
					<span class="jcs-badge jcs-badge--stock"><?php esc_html_e( 'In Stock', 'sunrooflighting' ); ?></span>
				<?php endif; ?>
			</div>

			<div class="jcs-product-actions">
				<?php if ( $is_quote_only ) : ?>
					<a href="<?php echo esc_url( jcs_quote_url( $product->get_name() ) ); ?>" class="button jcs-btn-quote">
						<?php esc_html_e( 'Request a Quote', 'sunrooflighting' ); ?>
					</a>
				<?php else : ?>
					<?php woocommerce_template_loop_add_to_cart(); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</li>
