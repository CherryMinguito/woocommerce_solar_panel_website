<?php
/**
 * Hero carousel — solar messaging.
 *
 * @package Sunrooflighting
 */
?>
<section class="jcs-hero">
	<div class="jcs-container">
		<div class="jcs-hero-carousel" data-carousel>
			<div class="jcs-hero-slide is-active" style="background: linear-gradient(135deg, #1c83c6 0%, #0e4d7a 100%);">
				<div class="jcs-hero-content">
					<h2><?php esc_html_e( 'Power Your Home with Solar', 'sunrooflighting' ); ?></h2>
					<p><?php esc_html_e( 'Professional solar installation with flexible financing. Start saving on day one.', 'sunrooflighting' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>" class="jcs-btn jcs-btn-light"><?php esc_html_e( 'Get a Free Quote', 'sunrooflighting' ); ?></a>
				</div>
			</div>
			<div class="jcs-hero-slide" style="background: linear-gradient(135deg, #f5a623 0%, #d4890a 100%);">
				<div class="jcs-hero-content">
					<h2><?php esc_html_e( 'See How Much You Can Save', 'sunrooflighting' ); ?></h2>
					<p><?php esc_html_e( 'Upload your utility bill and get an instant solar savings estimate.', 'sunrooflighting' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>" class="jcs-btn jcs-btn-light"><?php esc_html_e( 'Try the Calculator', 'sunrooflighting' ); ?></a>
				</div>
			</div>
			<div class="jcs-hero-slide" style="background: linear-gradient(135deg, #2e9e5b 0%, #1a6b3c 100%);">
				<div class="jcs-hero-content">
					<h2><?php esc_html_e( 'Flexible Financing Available', 'sunrooflighting' ); ?></h2>
					<p><?php esc_html_e( 'Pay with credit card or apply for affordable monthly solar financing.', 'sunrooflighting' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>" class="jcs-btn jcs-btn-light"><?php esc_html_e( 'Explore Financing', 'sunrooflighting' ); ?></a>
				</div>
			</div>
		</div>
		<div class="jcs-hero-dots" data-carousel-dots></div>
	</div>
</section>
