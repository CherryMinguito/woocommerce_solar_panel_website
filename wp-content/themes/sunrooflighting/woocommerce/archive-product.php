<?php
/**
 * Product archive layout.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_main_content' );
?>

<div class="jcs-plp">
	<div class="jcs-container">
		<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
			<nav class="jcs-breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'sunrooflighting' ); ?>">
				<?php woocommerce_breadcrumb(); ?>
			</nav>
		<?php endif; ?>

		<header class="jcs-plp-header">
			<div>
				<h1 class="jcs-plp-title"><?php echo esc_html( jcs_archive_heading() ); ?></h1>
				<p class="jcs-plp-count"><?php echo esc_html( jcs_archive_count_label() ); ?></p>
			</div>
			<div class="jcs-plp-toolbar">
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</header>

		<?php
		$top_categories = jcs_get_top_categories();
		if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) :
			?>
			<div class="jcs-top-categories">
				<span class="jcs-top-categories-label"><?php esc_html_e( 'Browse Categories', 'sunrooflighting' ); ?></span>
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
			<aside class="jcs-plp-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'sunrooflighting' ); ?>">
				<?php if ( is_active_sidebar( 'product-archive' ) ) : ?>
					<?php dynamic_sidebar( 'product-archive' ); ?>
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
						<p class="jcs-showing-count">
							<?php
							global $wp_query;
							printf(
								/* translators: 1: shown count, 2: total count */
								esc_html__( 'Showing %1$d of %2$d', 'sunrooflighting' ),
								(int) $wp_query->post_count,
								(int) $wp_query->found_posts
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
					if ( $term instanceof WP_Term ) :
						?>
						<div class="jcs-category-seo">
							<?php if ( ! empty( $term->description ) ) : ?>
								<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
							<?php endif; ?>
							<?php
							$extra = JCS_SEO::category_seo_content( $term );
							if ( $extra ) {
								echo wp_kses_post( $extra );
							}
							?>
						</div>
					<?php endif; ?>
				<?php elseif ( is_shop() ) : ?>
					<div class="jcs-category-seo">
						<h2><?php esc_html_e( 'Shop Solar Equipment & Installation Packages', 'sunrooflighting' ); ?></h2>
						<p><?php esc_html_e( 'Browse premium solar panels, inverters, batteries, and turnkey installation packages from Sunrooflighting. Installation packages require a personalized quote — equipment can be purchased directly online with credit card or financing.', 'sunrooflighting' ); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<?php
do_action( 'woocommerce_after_main_content' );
