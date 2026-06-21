<?php
/**
 * Get a quote page template.
 *
 * @package Sunrooflighting
 */

get_header();

$package = isset( $_GET['package'] ) ? sanitize_text_field( wp_unslash( $_GET['package'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$status  = isset( $_GET['quote'] ) ? sanitize_text_field( wp_unslash( $_GET['quote'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>

<main id="main-content" class="jcs-page jcs-page-quote">
	<div class="jcs-container">
		<header class="jcs-page-header">
			<h1><?php esc_html_e( 'Get Your Free Solar Quote', 'sunrooflighting' ); ?></h1>
			<p><?php esc_html_e( 'Tell us about your property and we\'ll prepare a personalized solar installation proposal.', 'sunrooflighting' ); ?></p>
		</header>

		<?php if ( 'success' === $status ) : ?>
			<div class="jcs-notice jcs-notice--success">
				<?php esc_html_e( 'Thank you! We\'ve received your quote request and will contact you within 1 business day.', 'sunrooflighting' ); ?>
			</div>
		<?php elseif ( 'error' === $status ) : ?>
			<div class="jcs-notice jcs-notice--error">
				<?php esc_html_e( 'Please fill in all required fields and try again.', 'sunrooflighting' ); ?>
			</div>
		<?php endif; ?>

		<?php echo do_shortcode( '[solar_quote_form package="' . esc_attr( $package ) . '"]' ); ?>
	</div>
</main>

<?php
get_footer();
