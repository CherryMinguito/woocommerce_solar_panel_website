<?php
/**
 * Top solar brands grid.
 *
 * @package Sunrooflighting
 */

$brands = array( 'SunPower', 'LG Solar', 'Tesla', 'Enphase', 'REC', 'Canadian Solar', 'Qcells', 'Panasonic' );
?>
<section class="jcs-section jcs-brands">
	<div class="jcs-container">
		<h2 class="jcs-section-title"><?php esc_html_e( 'Trusted Solar Brands', 'sunrooflighting' ); ?></h2>
		<div class="jcs-brand-grid">
			<?php foreach ( $brands as $brand ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'filter_brand', sanitize_title( $brand ), wc_get_page_permalink( 'shop' ) ) ); ?>" class="jcs-brand-tile">
					<span><?php echo esc_html( $brand ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
