<?php
/**
 * Site header.
 *
 * @package Sunrooflighting
 */
?>
<a class="screen-reader-text" href="#main-content"><?php esc_html_e( 'Skip to content', 'sunrooflighting' ); ?></a>

<div class="jcs-promo-bar">
	<div class="jcs-container">
		<div class="jcs-promo-carousel" data-carousel>
			<div class="jcs-promo-slide is-active">
				<strong><?php esc_html_e( 'Free Quotes:', 'sunrooflighting' ); ?></strong>
				<?php esc_html_e( 'Get a personalized solar installation proposal in 1 business day.', 'sunrooflighting' ); ?>
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Get Started', 'sunrooflighting' ); ?></a>
			</div>
			<div class="jcs-promo-slide">
				<strong><?php esc_html_e( 'Financing:', 'sunrooflighting' ); ?></strong>
				<?php esc_html_e( 'Affordable monthly payments available. Credit card accepted too.', 'sunrooflighting' ); ?>
				<a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>"><?php esc_html_e( 'Learn More', 'sunrooflighting' ); ?></a>
			</div>
			<div class="jcs-promo-slide">
				<strong><?php esc_html_e( 'Savings Calculator:', 'sunrooflighting' ); ?></strong>
				<?php esc_html_e( 'Upload your bill and see your potential solar savings instantly.', 'sunrooflighting' ); ?>
				<a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>"><?php esc_html_e( 'Try It', 'sunrooflighting' ); ?></a>
			</div>
		</div>
		<button class="jcs-promo-pause" data-carousel-pause aria-label="<?php esc_attr_e( 'Pause promo carousel', 'sunrooflighting' ); ?>">⏸</button>
	</div>
</div>

<header class="jcs-header">
	<div class="jcs-container jcs-header-inner">
		<div class="jcs-header-top">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="jcs-logo">
				<span class="jcs-logo-mark">☀️</span>
				<span class="jcs-logo-text">
					<strong><?php esc_html_e( 'Sunrooflighting', 'sunrooflighting' ); ?></strong>
					<small><?php esc_html_e( 'Solar Installation Experts', 'sunrooflighting' ); ?></small>
				</span>
			</a>

			<form role="search" method="get" class="jcs-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="jcs-search"><?php esc_html_e( 'Search products', 'sunrooflighting' ); ?></label>
				<input type="search" id="jcs-search" name="s" placeholder="<?php esc_attr_e( 'Search solar products...', 'sunrooflighting' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
				<input type="hidden" name="post_type" value="product">
				<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'sunrooflighting' ); ?>">🔍</button>
			</form>

			<div class="jcs-header-actions">
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>" class="jcs-header-link jcs-header-link--cta"><?php esc_html_e( 'Get a Quote', 'sunrooflighting' ); ?></a>
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="jcs-header-link"><?php esc_html_e( 'Account', 'sunrooflighting' ); ?></a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="jcs-header-link jcs-cart-link">
					🛒 <?php esc_html_e( 'Cart', 'sunrooflighting' ); ?>
					<?php if ( function_exists( 'WC' ) && WC()->cart ) : ?>
						<span class="jcs-cart-count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
					<?php endif; ?>
				</a>
			</div>
		</div>

		<nav class="jcs-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'sunrooflighting' ); ?>">
			<ul>
				<li><a href="<?php echo esc_url( jcs_term_link( 'installation-packages' ) ); ?>"><?php esc_html_e( 'Packages', 'sunrooflighting' ); ?></a></li>
				<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Equipment', 'sunrooflighting' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>"><?php esc_html_e( 'Calculator', 'sunrooflighting' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>"><?php esc_html_e( 'Financing', 'sunrooflighting' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Contact', 'sunrooflighting' ); ?></a></li>
			</ul>
		</nav>
	</div>
</header>
