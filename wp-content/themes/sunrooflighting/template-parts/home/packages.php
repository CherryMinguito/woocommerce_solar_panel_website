<?php
/**
 * Installation packages grid.
 *
 * @package Sunrooflighting
 */

$packages = jcs_get_package_products();
?>
<section class="jcs-section jcs-packages">
	<div class="jcs-container">
		<h2 class="jcs-section-title"><?php esc_html_e( 'Solar Installation Packages', 'sunrooflighting' ); ?></h2>
		<p class="jcs-section-subtitle"><?php esc_html_e( 'Turnkey solutions for every home and business. Request a personalized quote.', 'sunrooflighting' ); ?></p>
		<div class="jcs-package-grid">
			<?php if ( ! empty( $packages ) ) : ?>
				<?php foreach ( $packages as $pkg ) : ?>
					<div class="jcs-package-card">
						<h3><?php echo esc_html( $pkg->get_name() ); ?></h3>
						<div class="jcs-package-price">
							<?php echo wp_kses_post( $pkg->get_price_html() ); ?>
							<small><?php esc_html_e( 'starting at', 'sunrooflighting' ); ?></small>
						</div>
						<p class="jcs-package-desc"><?php echo esc_html( wp_strip_all_tags( $pkg->get_short_description() ?: $pkg->get_description() ) ); ?></p>
						<?php
						$kw = get_post_meta( $pkg->get_id(), '_jcs_system_kw', true );
						if ( $kw ) :
							?>
							<ul class="jcs-package-specs">
								<li><?php printf( esc_html__( '%s kW system', 'sunrooflighting' ), esc_html( $kw ) ); ?></li>
								<?php
								$panels = get_post_meta( $pkg->get_id(), '_jcs_panels', true );
								if ( $panels ) {
									printf( '<li>%s</li>', esc_html( sprintf( __( '%d panels', 'sunrooflighting' ), $panels ) ) );
								}
								?>
							</ul>
						<?php endif; ?>
						<a href="<?php echo esc_url( jcs_quote_url( $pkg->get_name() ) ); ?>" class="jcs-btn jcs-btn-quote"><?php esc_html_e( 'Request a Quote', 'sunrooflighting' ); ?></a>
					</div>
				<?php endforeach; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'Packages coming soon. Contact us for a custom quote.', 'sunrooflighting' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
