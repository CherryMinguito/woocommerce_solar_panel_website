<?php
/**
 * Quote request handler and custom post type.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

class JCS_Quote_Handler {

	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_shortcode( 'solar_quote_form', array( __CLASS__, 'render_form' ) );
		add_action( 'admin_post_nopriv_jcs_submit_quote', array( __CLASS__, 'handle_submission' ) );
		add_action( 'admin_post_jcs_submit_quote', array( __CLASS__, 'handle_submission' ) );
	}

	public static function register_post_type(): void {
		register_post_type(
			'quote_request',
			array(
				'labels'       => array(
					'name'          => __( 'Quote Requests', 'sunrooflighting' ),
					'singular_name' => __( 'Quote Request', 'sunrooflighting' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-email-alt',
				'supports'     => array( 'title', 'editor' ),
				'capability_type' => 'post',
			)
		);
	}

	public static function render_form( $atts = array() ): string {
		$atts = shortcode_atts(
			array(
				'package' => '',
			),
			$atts,
			'solar_quote_form'
		);

		$packages = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'slug'       => array( 'residential-packages', 'commercial-packages' ),
				'hide_empty' => false,
			)
		);

		$package_products = wc_get_products(
			array(
				'category' => array( 'residential-packages', 'commercial-packages' ),
				'limit'    => 20,
				'status'   => 'publish',
			)
		);

		ob_start();
		?>
		<form class="jcs-quote-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<?php wp_nonce_field( 'jcs_submit_quote', 'jcs_quote_nonce' ); ?>
			<input type="hidden" name="action" value="jcs_submit_quote">

			<div class="jcs-form-row jcs-form-row--half">
				<div class="jcs-form-field">
					<label for="jcs-quote-name"><?php esc_html_e( 'Full Name', 'sunrooflighting' ); ?> *</label>
					<input type="text" id="jcs-quote-name" name="name" required>
				</div>
				<div class="jcs-form-field">
					<label for="jcs-quote-email"><?php esc_html_e( 'Email', 'sunrooflighting' ); ?> *</label>
					<input type="email" id="jcs-quote-email" name="email" required>
				</div>
			</div>

			<div class="jcs-form-row jcs-form-row--half">
				<div class="jcs-form-field">
					<label for="jcs-quote-phone"><?php esc_html_e( 'Phone', 'sunrooflighting' ); ?> *</label>
					<input type="tel" id="jcs-quote-phone" name="phone" required>
				</div>
				<div class="jcs-form-field">
					<label for="jcs-quote-package"><?php esc_html_e( 'Package Interest', 'sunrooflighting' ); ?></label>
					<select id="jcs-quote-package" name="package">
						<option value=""><?php esc_html_e( 'Not sure yet', 'sunrooflighting' ); ?></option>
						<?php foreach ( $package_products as $pkg ) : ?>
							<option value="<?php echo esc_attr( $pkg->get_name() ); ?>" <?php selected( $atts['package'], $pkg->get_name() ); ?>>
								<?php echo esc_html( $pkg->get_name() ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>

			<div class="jcs-form-field">
				<label for="jcs-quote-address"><?php esc_html_e( 'Property Address', 'sunrooflighting' ); ?> *</label>
				<input type="text" id="jcs-quote-address" name="address" required placeholder="<?php esc_attr_e( 'Street, City, State, ZIP', 'sunrooflighting' ); ?>">
			</div>

			<div class="jcs-form-field">
				<label for="jcs-quote-message"><?php esc_html_e( 'Tell us about your project', 'sunrooflighting' ); ?></label>
				<textarea id="jcs-quote-message" name="message" rows="4" placeholder="<?php esc_attr_e( 'Roof type, energy goals, timeline...', 'sunrooflighting' ); ?>"></textarea>
			</div>

			<div class="jcs-form-field">
				<label for="jcs-quote-bill"><?php esc_html_e( 'Upload Utility Bill (optional)', 'sunrooflighting' ); ?></label>
				<input type="file" id="jcs-quote-bill" name="utility_bill" accept=".pdf,.jpg,.jpeg,.png">
				<small><?php esc_html_e( 'PDF, JPG, or PNG — helps us prepare an accurate quote.', 'sunrooflighting' ); ?></small>
			</div>

			<button type="submit" class="jcs-btn jcs-btn-quote"><?php esc_html_e( 'Request My Quote', 'sunrooflighting' ); ?></button>
		</form>
		<?php
		return ob_get_clean();
	}

	public static function handle_submission(): void {
		if ( ! isset( $_POST['jcs_quote_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcs_quote_nonce'] ) ), 'jcs_submit_quote' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'sunrooflighting' ) );
		}

		$name    = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
		$email   = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$phone   = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
		$address = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
		$package = sanitize_text_field( wp_unslash( $_POST['package'] ?? '' ) );
		$message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );

		if ( empty( $name ) || empty( $email ) || empty( $phone ) || empty( $address ) ) {
			wp_safe_redirect( add_query_arg( 'quote', 'error', wp_get_referer() ?: home_url( '/quote/' ) ) );
			exit;
		}

		$content  = "Email: {$email}\nPhone: {$phone}\nAddress: {$address}\n";
		$content .= "Package: " . ( $package ?: 'Not specified' ) . "\n\n";
		$content .= $message;

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'quote_request',
				'post_title'   => "Quote: {$name}",
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);

		if ( ! empty( $_FILES['utility_bill']['name'] ) && $post_id ) {
			self::handle_bill_upload( $post_id );
		}

		$notify_email = getenv( 'QUOTE_NOTIFICATION_EMAIL' ) ?: get_option( 'admin_email' );
		wp_mail(
			$notify_email,
			"New Quote Request from {$name}",
			$content,
			array( 'Reply-To: ' . $email )
		);

		wp_safe_redirect( add_query_arg( 'quote', 'success', home_url( '/quote/' ) ) );
		exit;
	}

	private static function handle_bill_upload( int $post_id ): void {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$upload = wp_handle_upload(
			$_FILES['utility_bill'],
			array( 'test_form' => false, 'mimes' => array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' ) )
		);

		if ( isset( $upload['file'] ) ) {
			update_post_meta( $post_id, '_utility_bill_path', $upload['file'] );
			update_post_meta( $post_id, '_utility_bill_url', $upload['url'] );
		}
	}
}
