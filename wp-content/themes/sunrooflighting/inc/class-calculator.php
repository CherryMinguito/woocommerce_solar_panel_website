<?php
/**
 * Solar savings calculator shortcode and REST API.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

class JCS_Calculator {

	public static function init(): void {
		add_shortcode( 'solar_calculator', array( __CLASS__, 'render_calculator' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_routes' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	public static function enqueue_assets(): void {
		$post = get_post();
		$has_shortcode = $post && has_shortcode( $post->post_content, 'solar_calculator' );

		if ( ! is_page_template( 'page-calculator.php' ) && ! $has_shortcode ) {
			return;
		}

		wp_enqueue_style( 'jcs-calculator', JCS_URI . '/assets/css/calculator.css', array( 'jcs-style' ), JCS_VERSION );
		wp_enqueue_script(
			'jcs-calculator',
			JCS_URI . '/assets/js/calculator.js',
			array(),
			JCS_VERSION,
			true
		);

		wp_localize_script(
			'jcs-calculator',
			'jcsCalculator',
			array(
				'restUrl' => rest_url( 'sunrooflighting/v1/' ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	public static function register_routes(): void {
		register_rest_route(
			'sunrooflighting/v1',
			'/calculate',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_calculate' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			'sunrooflighting/v1',
			'/ocr',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'rest_ocr' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function render_calculator(): string {
		ob_start();
		?>
		<div class="jcs-calculator" id="jcs-calculator">
			<div class="jcs-calculator-upload">
				<h3><?php esc_html_e( 'Upload Your Utility Bill', 'sunrooflighting' ); ?></h3>
				<p><?php esc_html_e( 'We\'ll read your usage automatically, or enter details manually below.', 'sunrooflighting' ); ?></p>
				<div class="jcs-upload-zone" id="jcs-upload-zone">
					<input type="file" id="jcs-bill-upload" accept=".pdf,.jpg,.jpeg,.png" hidden>
					<label for="jcs-bill-upload" class="jcs-upload-label">
						<span class="jcs-upload-icon">📄</span>
						<span><?php esc_html_e( 'Drop your bill here or click to upload', 'sunrooflighting' ); ?></span>
						<small>PDF, JPG, PNG</small>
					</label>
				</div>
				<div class="jcs-ocr-status" id="jcs-ocr-status" hidden></div>
			</div>

			<div class="jcs-calculator-inputs">
				<h3><?php esc_html_e( 'Your Energy Usage', 'sunrooflighting' ); ?></h3>
				<div class="jcs-form-row jcs-form-row--half">
					<div class="jcs-form-field">
						<label for="jcs-monthly-kwh"><?php esc_html_e( 'Monthly Usage (kWh)', 'sunrooflighting' ); ?></label>
						<input type="number" id="jcs-monthly-kwh" min="0" step="1" placeholder="e.g. 900">
					</div>
					<div class="jcs-form-field">
						<label for="jcs-monthly-bill"><?php esc_html_e( 'Monthly Bill ($)', 'sunrooflighting' ); ?></label>
						<input type="number" id="jcs-monthly-bill" min="0" step="0.01" placeholder="e.g. 150">
					</div>
				</div>
				<button type="button" class="jcs-btn" id="jcs-calculate-btn"><?php esc_html_e( 'Calculate My Savings', 'sunrooflighting' ); ?></button>
			</div>

			<div class="jcs-calculator-results" id="jcs-calculator-results" hidden>
				<h3><?php esc_html_e( 'Your Solar Estimate', 'sunrooflighting' ); ?></h3>
				<div class="jcs-results-grid">
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-kw">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Recommended System', 'sunrooflighting' ); ?></span>
					</div>
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-panels">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Solar Panels', 'sunrooflighting' ); ?></span>
					</div>
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-cost">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Est. Install Cost', 'sunrooflighting' ); ?></span>
					</div>
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-savings">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Annual Savings', 'sunrooflighting' ); ?></span>
					</div>
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-payback">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Payback Period', 'sunrooflighting' ); ?></span>
					</div>
					<div class="jcs-result-card">
						<span class="jcs-result-value" id="jcs-result-monthly">—</span>
						<span class="jcs-result-label"><?php esc_html_e( 'Monthly Savings', 'sunrooflighting' ); ?></span>
					</div>
				</div>
				<p class="jcs-results-disclaimer"><?php esc_html_e( 'Estimates are based on average installation costs and local utility rates. Request a quote for a precise assessment.', 'sunrooflighting' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/quote/' ) ); ?>" class="jcs-btn jcs-btn-quote"><?php esc_html_e( 'Get Your Free Quote', 'sunrooflighting' ); ?></a>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function rest_calculate( WP_REST_Request $request ): WP_REST_Response {
		$monthly_kwh  = (float) $request->get_param( 'monthly_kwh' );
		$monthly_bill = $request->get_param( 'monthly_bill' ) ? (float) $request->get_param( 'monthly_bill' ) : null;

		if ( $monthly_kwh <= 0 ) {
			return new WP_REST_Response( array( 'error' => 'Monthly kWh is required.' ), 400 );
		}

		$estimate = JCS_OCR_Processor::calculate_estimate( $monthly_kwh, $monthly_bill );

		return new WP_REST_Response( $estimate );
	}

	public static function rest_ocr( WP_REST_Request $request ): WP_REST_Response {
		$files = $request->get_file_params();

		if ( empty( $files['bill'] ) ) {
			return new WP_REST_Response( array( 'error' => 'No file uploaded.' ), 400 );
		}

		$file = $files['bill'];

		if ( ! in_array( $file['type'], array( 'application/pdf', 'image/jpeg', 'image/png' ), true ) ) {
			return new WP_REST_Response( array( 'error' => 'Invalid file type.' ), 400 );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$upload = wp_handle_upload(
			$file,
			array( 'test_form' => false, 'mimes' => array( 'pdf' => 'application/pdf', 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png' ) )
		);

		if ( isset( $upload['error'] ) ) {
			return new WP_REST_Response( array( 'error' => $upload['error'] ), 400 );
		}

		$ocr_data = JCS_OCR_Processor::extract_bill_data( $upload['file'] );
		@unlink( $upload['file'] );

		$estimate = null;
		if ( $ocr_data['kwh'] ) {
			$estimate = JCS_OCR_Processor::calculate_estimate( $ocr_data['kwh'], $ocr_data['amount'] );
		}

		return new WP_REST_Response(
			array(
				'ocr'      => $ocr_data,
				'estimate' => $estimate,
			)
		);
	}
}
