<?php
/**
 * Savings calculator page template.
 *
 * @package Sunrooflighting
 */

get_header();
?>

<main id="main-content" class="jcs-page jcs-page-calculator">
	<div class="jcs-container">
		<header class="jcs-page-header">
			<h1><?php esc_html_e( 'Solar Savings Calculator', 'sunrooflighting' ); ?></h1>
			<p><?php esc_html_e( 'Upload your utility bill or enter your usage to see how much you could save with solar.', 'sunrooflighting' ); ?></p>
		</header>
		<?php echo do_shortcode( '[solar_calculator]' ); ?>

		<div class="jcs-page-seo-content">
			<h2><?php esc_html_e( 'How the Solar Savings Calculator Works', 'sunrooflighting' ); ?></h2>
			<p><?php esc_html_e( 'Upload a PDF or image of your utility bill and our system reads your kWh usage and bill amount to recommend a system size, estimated installation cost, annual savings, and payback period. You can also enter your monthly usage manually.', 'sunrooflighting' ); ?></p>
			<ul>
				<li><?php esc_html_e( 'Instant estimate based on your actual energy usage', 'sunrooflighting' ); ?></li>
				<li><?php esc_html_e( 'Recommended system size in kilowatts (kW)', 'sunrooflighting' ); ?></li>
				<li><?php esc_html_e( 'Projected annual and monthly savings', 'sunrooflighting' ); ?></li>
			</ul>
		</div>
	</div>
</main>

<?php
get_footer();
