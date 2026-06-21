<?php
/**
 * Generic solar financing payment gateway (provider-agnostic scaffold).
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

class WC_Gateway_JCS_Financing extends WC_Payment_Gateway {

	public function __construct() {
		$this->id                 = 'jcs_financing';
		$this->icon                 = '';
		$this->has_fields           = true;
		$this->method_title         = __( 'Solar Financing', 'sunrooflighting' );
		$this->method_description   = __( 'Allow customers to apply for monthly solar financing. Connect your lender API in .env.', 'sunrooflighting' );
		$this->supports             = array( 'products' );

		$this->init_form_fields();
		$this->init_settings();

		$this->title       = $this->get_option( 'title', __( 'Solar Financing', 'sunrooflighting' ) );
		$this->description = $this->get_option( 'description', __( 'Apply for affordable monthly payments on your solar installation.', 'sunrooflighting' ) );
		$this->enabled     = $this->get_option( 'enabled', 'yes' );

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	}

	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'sunrooflighting' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Solar Financing', 'sunrooflighting' ),
				'default' => 'yes',
			),
			'title'       => array(
				'title'       => __( 'Title', 'sunrooflighting' ),
				'type'        => 'text',
				'description' => __( 'Payment method title shown at checkout.', 'sunrooflighting' ),
				'default'     => __( 'Solar Financing', 'sunrooflighting' ),
			),
			'description' => array(
				'title'       => __( 'Description', 'sunrooflighting' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description shown at checkout.', 'sunrooflighting' ),
				'default'     => __( 'Apply for affordable monthly payments. Subject to credit approval.', 'sunrooflighting' ),
			),
		);
	}

	public function payment_fields(): void {
		if ( $this->description ) {
			echo wp_kses_post( wpautop( $this->description ) );
		}

		$provider = getenv( 'FINANCING_PROVIDER_NAME' ) ?: __( 'our financing partner', 'sunrooflighting' );
		?>
		<fieldset class="jcs-financing-fields">
			<p class="form-row form-row-wide">
				<label for="jcs_financing_name"><?php esc_html_e( 'Full Name', 'sunrooflighting' ); ?> <span class="required">*</span></label>
				<input type="text" class="input-text" name="jcs_financing_name" id="jcs_financing_name" required>
			</p>
			<p class="form-row form-row-wide">
				<label for="jcs_financing_email"><?php esc_html_e( 'Email', 'sunrooflighting' ); ?> <span class="required">*</span></label>
				<input type="email" class="input-text" name="jcs_financing_email" id="jcs_financing_email" required>
			</p>
			<p class="form-row form-row-wide">
				<label for="jcs_financing_phone"><?php esc_html_e( 'Phone', 'sunrooflighting' ); ?> <span class="required">*</span></label>
				<input type="tel" class="input-text" name="jcs_financing_phone" id="jcs_financing_phone" required>
			</p>
			<p class="form-row form-row-wide">
				<label for="jcs_financing_income"><?php esc_html_e( 'Annual Household Income ($)', 'sunrooflighting' ); ?></label>
				<input type="number" class="input-text" name="jcs_financing_income" id="jcs_financing_income" min="0" step="1000">
			</p>
			<p class="jcs-financing-note">
				<?php
				printf(
					/* translators: %s: financing provider name */
					esc_html__( 'Your application will be submitted to %s for pre-qualification. This does not affect your credit score.', 'sunrooflighting' ),
					esc_html( $provider )
				);
				?>
			</p>
		</fieldset>
		<?php
	}

	public function validate_fields(): bool {
		if ( empty( $_POST['jcs_financing_name'] ) || empty( $_POST['jcs_financing_email'] ) || empty( $_POST['jcs_financing_phone'] ) ) {
			wc_add_notice( __( 'Please complete all required financing fields.', 'sunrooflighting' ), 'error' );
			return false;
		}
		return true;
	}

	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );

		$application = array(
			'name'   => sanitize_text_field( wp_unslash( $_POST['jcs_financing_name'] ?? '' ) ),
			'email'  => sanitize_email( wp_unslash( $_POST['jcs_financing_email'] ?? '' ) ),
			'phone'  => sanitize_text_field( wp_unslash( $_POST['jcs_financing_phone'] ?? '' ) ),
			'income' => sanitize_text_field( wp_unslash( $_POST['jcs_financing_income'] ?? '' ) ),
			'amount' => $order->get_total(),
		);

		update_post_meta( $order_id, '_jcs_financing_application', $application );

		$api_result = $this->submit_to_provider( $application, $order );

		if ( is_wp_error( $api_result ) ) {
			wc_add_notice( $api_result->get_error_message(), 'error' );
			return array( 'result' => 'fail' );
		}

		$order->update_status( 'on-hold', __( 'Financing application submitted — awaiting approval.', 'sunrooflighting' ) );
		$order->add_order_note( __( 'Solar financing application submitted.', 'sunrooflighting' ) );
		$order->reduce_order_stock();
		WC()->cart->empty_cart();

		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Submit financing application to external provider.
	 * Override or extend when FINANCING_API_URL and FINANCING_API_KEY are configured.
	 *
	 * @param array<string, mixed> $application Application data.
	 * @param WC_Order             $order       WooCommerce order.
	 * @return true|WP_Error
	 */
	private function submit_to_provider( array $application, WC_Order $order ) {
		$api_url = getenv( 'FINANCING_API_URL' );
		$api_key = getenv( 'FINANCING_API_KEY' );

		if ( empty( $api_url ) || empty( $api_key ) ) {
			$notify_email = getenv( 'QUOTE_NOTIFICATION_EMAIL' ) ?: get_option( 'admin_email' );
			wp_mail(
				$notify_email,
				'New Financing Application — Order #' . $order->get_id(),
				wp_json_encode( $application, JSON_PRETTY_PRINT )
			);
			return true;
		}

		$response = wp_remote_post(
			$api_url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode(
					array_merge(
						$application,
						array(
							'merchant_id' => getenv( 'FINANCING_MERCHANT_ID' ) ?: '',
							'order_id'    => $order->get_id(),
						)
					)
				),
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error( 'financing_api_error', __( 'Financing provider returned an error. Please try again or contact us.', 'sunrooflighting' ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( ! empty( $body['application_id'] ) ) {
			update_post_meta( $order->get_id(), '_jcs_financing_application_id', sanitize_text_field( $body['application_id'] ) );
		}

		return true;
	}
}

function jcs_register_financing_gateway( array $gateways ): array {
	$gateways[] = 'WC_Gateway_JCS_Financing';
	return $gateways;
}
