<?php
/**
 * Site footer.
 *
 * @package Sunrooflighting
 */
?>
<footer class="jcs-footer">
	<div class="jcs-container">
		<div class="jcs-footer-grid">
			<div class="jcs-footer-col">
				<h4><?php esc_html_e( 'Solar Solutions', 'sunrooflighting' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( jcs_term_link( 'installation-packages' ) ); ?>"><?php esc_html_e( 'Installation Packages', 'sunrooflighting' ); ?></a></li>
					<li><a href="<?php echo esc_url( jcs_term_link( 'solar-panels' ) ); ?>"><?php esc_html_e( 'Solar Panels', 'sunrooflighting' ); ?></a></li>
					<li><a href="<?php echo esc_url( jcs_term_link( 'batteries-storage' ) ); ?>"><?php esc_html_e( 'Batteries & Storage', 'sunrooflighting' ); ?></a></li>
					<li><a href="<?php echo esc_url( jcs_term_link( 'inverters' ) ); ?>"><?php esc_html_e( 'Inverters', 'sunrooflighting' ); ?></a></li>
				</ul>
			</div>
			<div class="jcs-footer-col">
				<h4><?php esc_html_e( 'Get Started', 'sunrooflighting' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>"><?php esc_html_e( 'Request a Quote', 'sunrooflighting' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>"><?php esc_html_e( 'Savings Calculator', 'sunrooflighting' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>"><?php esc_html_e( 'Financing Options', 'sunrooflighting' ); ?></a></li>
				</ul>
			</div>
			<div class="jcs-footer-col">
				<h4><?php esc_html_e( 'About Sunrooflighting', 'sunrooflighting' ); ?></h4>
				<ul>
					<li><a href="#"><?php esc_html_e( 'Our Story', 'sunrooflighting' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Service Areas', 'sunrooflighting' ); ?></a></li>
					<li><a href="#"><?php esc_html_e( 'Warranties', 'sunrooflighting' ); ?></a></li>
				</ul>
			</div>
			<div class="jcs-footer-col">
				<h4><?php esc_html_e( 'Contact Us', 'sunrooflighting' ); ?></h4>
				<p><?php esc_html_e( 'Ready to go solar? Get your free quote today.', 'sunrooflighting' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>" class="button jcs-btn-quote"><?php esc_html_e( 'Get a Quote', 'sunrooflighting' ); ?></a>
			</div>
		</div>
		<div class="jcs-footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php esc_html_e( 'Sunrooflighting. All rights reserved.', 'sunrooflighting' ); ?></p>
		</div>
	</div>
</footer>
