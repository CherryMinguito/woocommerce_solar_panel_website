<?php
/**
 * Homepage SEO content block.
 *
 * @package Sunrooflighting
 */
?>
<section class="jcs-section jcs-seo-content" aria-labelledby="jcs-seo-heading">
	<div class="jcs-container">
		<article class="jcs-seo-article">
			<h2 id="jcs-seo-heading"><?php esc_html_e( 'Professional Solar Installation in Arizona', 'sunrooflighting' ); ?></h2>
			<p><?php esc_html_e( 'Sunrooflighting is a full-service solar installer helping homeowners and businesses switch to clean energy. We design, permit, install, and activate solar panel systems that lower your electric bills and increase property value.', 'sunrooflighting' ); ?></p>

			<div class="jcs-seo-columns">
				<div>
					<h3><?php esc_html_e( 'Residential Solar Installation', 'sunrooflighting' ); ?></h3>
					<p><?php esc_html_e( 'Our residential packages range from 4 kW starter systems to whole-home 16 kW installations with battery backup. Use our free savings calculator to upload your utility bill and estimate system size, cost, and payback period.', 'sunrooflighting' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( 'Commercial Solar Solutions', 'sunrooflighting' ); ?></h3>
					<p><?php esc_html_e( 'Offices, warehouses, and retail spaces benefit from commercial solar with reduced operating costs and predictable energy pricing. We handle engineering, permitting, and utility interconnection.', 'sunrooflighting' ); ?></p>
				</div>
				<div>
					<h3><?php esc_html_e( 'Flexible Payment Options', 'sunrooflighting' ); ?></h3>
					<p><?php esc_html_e( 'Pay by credit card at checkout or apply for monthly solar financing with competitive rates. Every installation includes a 25-year performance warranty and ongoing monitoring support.', 'sunrooflighting' ); ?></p>
				</div>
			</div>

			<p class="jcs-seo-cta">
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Request your free solar quote', 'sunrooflighting' ); ?></a>
				<?php esc_html_e( 'or', 'sunrooflighting' ); ?>
				<a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>"><?php esc_html_e( 'try the savings calculator', 'sunrooflighting' ); ?></a>.
			</p>
		</article>
	</div>
</section>
