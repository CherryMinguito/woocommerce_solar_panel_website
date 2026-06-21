<?php
/**
 * Financing information page template.
 *
 * @package Sunrooflighting
 */

get_header();
?>

<main id="main-content" class="jcs-page jcs-page-financing">
	<div class="jcs-container">
		<header class="jcs-page-header">
			<h1><?php esc_html_e( 'Solar Financing Options', 'sunrooflighting' ); ?></h1>
			<p><?php esc_html_e( 'Make solar affordable with flexible payment options. Pay by credit card or apply for monthly financing.', 'sunrooflighting' ); ?></p>
		</header>

		<div class="jcs-financing-options">
			<div class="jcs-financing-card">
				<h2>💳 <?php esc_html_e( 'Credit & Debit Card', 'sunrooflighting' ); ?></h2>
				<p><?php esc_html_e( 'Pay in full at checkout using your credit or debit card via our secure Stripe payment gateway. Ideal for equipment purchases and deposits.', 'sunrooflighting' ); ?></p>
			</div>

			<div class="jcs-financing-card jcs-financing-card--highlight">
				<h2>📅 <?php esc_html_e( 'Monthly Solar Financing', 'sunrooflighting' ); ?></h2>
				<p><?php esc_html_e( 'Spread the cost of your solar installation over 10–25 years with competitive rates. Apply at checkout and get a decision quickly.', 'sunrooflighting' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'No prepayment penalties', 'sunrooflighting' ); ?></li>
					<li><?php esc_html_e( 'Fixed monthly payments', 'sunrooflighting' ); ?></li>
					<li><?php esc_html_e( 'Subject to credit approval', 'sunrooflighting' ); ?></li>
				</ul>
			</div>
		</div>

		<div class="jcs-financing-example">
			<h2><?php esc_html_e( 'Example Monthly Payments', 'sunrooflighting' ); ?></h2>
			<div class="jcs-results-grid">
				<div class="jcs-result-card">
					<span class="jcs-result-value">$50/mo</span>
					<span class="jcs-result-label"><?php esc_html_e( '4 kW Starter Package', 'sunrooflighting' ); ?></span>
				</div>
				<div class="jcs-result-card">
					<span class="jcs-result-value">$92/mo</span>
					<span class="jcs-result-label"><?php esc_html_e( '8 kW Family Package', 'sunrooflighting' ); ?></span>
				</div>
				<div class="jcs-result-card">
					<span class="jcs-result-value">$133/mo</span>
					<span class="jcs-result-label"><?php esc_html_e( '12 kW Whole-Home Package', 'sunrooflighting' ); ?></span>
				</div>
			</div>
			<p class="jcs-results-disclaimer"><?php esc_html_e( 'Examples based on 20-year term at estimated rates. Actual payments depend on credit approval and lender terms.', 'sunrooflighting' ); ?></p>
		</div>

		<div class="jcs-financing-cta">
			<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>" class="jcs-btn"><?php esc_html_e( 'Get Your Free Quote', 'sunrooflighting' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/calculator/' ) ); ?>" class="jcs-btn jcs-btn-light"><?php esc_html_e( 'Calculate Savings', 'sunrooflighting' ); ?></a>
		</div>
	</div>
</main>

<?php
get_footer();
