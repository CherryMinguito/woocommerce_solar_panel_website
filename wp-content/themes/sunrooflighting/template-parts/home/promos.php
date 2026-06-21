<?php
/**
 * Promo banners — calculator and financing CTAs.
 *
 * @package Sunrooflighting
 */
?>
<section class="jcs-section jcs-promos">
	<div class="jcs-container">
		<h2 class="jcs-section-title"><?php esc_html_e( 'Start Your Solar Journey', 'sunrooflighting' ); ?></h2>
		<div class="jcs-promo-grid">
			<div class="jcs-promo-card">
				<h3><?php esc_html_e( 'Savings Calculator', 'sunrooflighting' ); ?></h3>
				<p><?php esc_html_e( 'Upload your utility bill and get an instant estimate of system size, cost, and annual savings.', 'sunrooflighting' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>"><?php esc_html_e( 'Calculate Savings', 'sunrooflighting' ); ?> →</a>
			</div>
			<div class="jcs-promo-card">
				<h3><?php esc_html_e( 'Free Installation Quote', 'sunrooflighting' ); ?></h3>
				<p><?php esc_html_e( 'Our solar experts will assess your property and prepare a custom proposal within 1 business day.', 'sunrooflighting' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Get a Quote', 'sunrooflighting' ); ?> →</a>
			</div>
			<div class="jcs-promo-card jcs-promo-card--accent">
				<h3><?php esc_html_e( 'Solar Financing', 'sunrooflighting' ); ?></h3>
				<p><?php esc_html_e( 'Flexible monthly payments with competitive rates. Credit card and financing options available at checkout.', 'sunrooflighting' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>"><?php esc_html_e( 'View Financing Options', 'sunrooflighting' ); ?> →</a>
			</div>
		</div>
	</div>
</section>

<section class="jcs-section jcs-featured-deals">
	<div class="jcs-container">
		<h2 class="jcs-section-title"><?php esc_html_e( 'Popular Solar Equipment', 'sunrooflighting' ); ?></h2>
		<?php echo do_shortcode( '[products limit="4" columns="4" category="solar-panels" orderby="popularity"]' ); ?>
	</div>
</section>
