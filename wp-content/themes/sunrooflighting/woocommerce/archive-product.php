<?php
/**
 * Product archive override — Ace-style PLP layout.
 *
 * @package Jed_Construction_Supply
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_main_content' );
?>

<div class="jcs-plp">
	<div class="jcs-container">
		<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
			<nav class="jcs-breadcrumb">
				<?php woocommerce_breadcrumb(); ?>
			</nav>
		<?php endif; ?>

		<header class="jcs-plp-header">
			<h1 class="jcs-plp-title"><?php echo esc_html( jcs_archive_title() ); ?></h1>
			<div class="jcs-plp-toolbar">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</header>

		<?php
		$top_categories = jcs_get_top_categories();
		if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) :
			?>
			<div class="jcs-top-categories">
				<span class="jcs-top-categories-label"><?php esc_html_e( 'Top Categories', 'jed-construction-supply' ); ?></span>
				<ul class="jcs-top-categories-list">
					<?php foreach ( $top_categories as $cat ) : ?>
						<li>
							<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" <?php echo is_product_category( $cat->slug ) ? 'class="is-active"' : ''; ?>>
								<?php echo esc_html( $cat->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="jcs-plp-layout">
			<aside class="jcs-plp-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'jed-construction-supply' ); ?>">
				<?php if ( is_active_sidebar( 'product-archive' ) ) : ?>
					<?php dynamic_sidebar( 'product-archive' ); ?>
				<?php else : ?>
					<div class="jcs-filter-widget">
						<h3 class="jcs-filter-title"><?php esc_html_e( 'Get It Fast', 'jed-construction-supply' ); ?></h3>
						<label><input type="checkbox" disabled> <?php esc_html_e( 'In stock today', 'jed-construction-supply' ); ?></label>
					</div>
				<?php endif; ?>
			</aside>

			<div class="jcs-plp-main">
				<?php if ( woocommerce_product_loop() ) : ?>

					<?php do_action( 'woocommerce_before_shop_loop' ); ?>

					<ul class="products jcs-product-grid columns-<?php echo esc_attr( wc_get_default_products_per_row() ); ?>">
						<?php
						while ( have_posts() ) {
							the_post();
							wc_get_template_part( 'content', 'product' );
						}
						?>
					</ul>

					<?php do_action( 'woocommerce_after_shop_loop' ); ?>

					<div class="jcs-load-more">
						<?php
						global $wp_query;
						$shown = $wp_query->post_count;
						$total = $wp_query->found_posts;
						?>
						<p class="jcs-showing-count">
							<?php
							printf(
								/* translators: 1: shown count, 2: total count */
								esc_html__( 'Showing %1$d of %2$d', 'jed-construction-supply' ),
								(int) $shown,
								(int) $total
							);
							?>
						</p>
						<?php woocommerce_pagination(); ?>
					</div>

				<?php else : ?>
					<?php do_action( 'woocommerce_no_products_found' ); ?>
				<?php endif; ?>

				<?php if ( is_product_category() ) : ?>
					<?php
					$term = get_queried_object();
					if ( $term && ! empty( $term->description ) ) :
						?>
						<div class="jcs-category-seo">
							<?php echo wp_kses_post( wpautop( $term->description ) ); ?>

							<?php if ( 'windows-and-doors' === $term->slug ) : ?>
								<h2><?php esc_html_e( 'Upgrade Your Interior and Exterior Doors', 'jed-construction-supply' ); ?></h2>
								<p><?php esc_html_e( 'When upgrading or replacing doors in your home, consider a range of options including interior doors, screen doors, storm doors, sliding doors, and bifold doors.', 'jed-construction-supply' ); ?></p>

								<h3><?php esc_html_e( 'Interior Doors', 'jed-construction-supply' ); ?></h3>
								<p><?php esc_html_e( 'Designed for use inside homes, interior doors feature solid wood or wood veneers in a range of finishes and styles.', 'jed-construction-supply' ); ?></p>

								<h3><?php esc_html_e( 'Find Window Glass Replacements', 'jed-construction-supply' ); ?></h3>
								<p><?php esc_html_e( 'When your window frames are still in good condition, purchasing glass separately can help you save money. Choose traditional glass or durable acrylic based on your project needs.', 'jed-construction-supply' ); ?></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
