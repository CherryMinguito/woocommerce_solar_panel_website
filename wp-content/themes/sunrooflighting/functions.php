<?php
/**
 * Sunrooflighting theme functions.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

define( 'JCS_VERSION', '2.2.1' );
define( 'JCS_DIR', get_stylesheet_directory() );
define( 'JCS_URI', get_stylesheet_directory_uri() );

require_once JCS_DIR . '/inc/class-ocr-processor.php';
require_once JCS_DIR . '/inc/class-quote-handler.php';
require_once JCS_DIR . '/inc/class-calculator.php';
require_once JCS_DIR . '/inc/class-seo.php';

function jcs_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'woocommerce' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'sunrooflighting' ),
			'footer'  => __( 'Footer Menu', 'sunrooflighting' ),
		)
	);
}
add_action( 'after_setup_theme', 'jcs_setup' );

function jcs_enqueue_assets(): void {
	wp_enqueue_style( 'storefront-style', get_template_directory_uri() . '/style.css', array(), JCS_VERSION );
	wp_enqueue_style( 'jcs-style', get_stylesheet_uri(), array( 'storefront-style' ), JCS_VERSION );
	wp_enqueue_style( 'jcs-home', JCS_URI . '/assets/css/home.css', array( 'jcs-style' ), JCS_VERSION );
	wp_enqueue_style( 'jcs-archive', JCS_URI . '/assets/css/archive.css', array( 'jcs-style' ), JCS_VERSION );
	wp_enqueue_style( 'jcs-forms', JCS_URI . '/assets/css/forms.css', array( 'jcs-style' ), JCS_VERSION );
	wp_enqueue_style( 'jcs-mobile', JCS_URI . '/assets/css/mobile.css', array( 'jcs-home', 'jcs-archive', 'jcs-forms' ), JCS_VERSION );
	wp_enqueue_style( 'jcs-seo', JCS_URI . '/assets/css/seo.css', array( 'jcs-style' ), JCS_VERSION );

	if ( is_page_template( array( 'page-calculator.php', 'page-financing.php' ) ) ) {
		wp_enqueue_style( 'jcs-calculator', JCS_URI . '/assets/css/calculator.css', array( 'jcs-style' ), JCS_VERSION );
	}

	wp_enqueue_script( 'jcs-carousel', JCS_URI . '/assets/js/carousel.js', array(), JCS_VERSION, true );
	wp_enqueue_script( 'jcs-mobile-nav', JCS_URI . '/assets/js/mobile-nav.js', array(), JCS_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'jcs_enqueue_assets' );

function jcs_init_features(): void {
	JCS_Quote_Handler::init();
	JCS_Calculator::init();
	JCS_SEO::init();
}
add_action( 'init', 'jcs_init_features' );

function jcs_load_financing_gateway(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	require_once JCS_DIR . '/inc/class-financing-gateway.php';
	add_filter( 'woocommerce_payment_gateways', 'jcs_register_financing_gateway' );
}
add_action( 'plugins_loaded', 'jcs_load_financing_gateway' );

/**
 * Installation packages are quote-only — not directly purchasable.
 */
function jcs_is_quote_only_product( $product ): bool {
	if ( is_numeric( $product ) ) {
		$product = wc_get_product( $product );
	}
	if ( ! $product ) {
		return false;
	}

	$quote_cats = array( 'installation-packages', 'residential-packages', 'commercial-packages' );
	$terms      = get_the_terms( $product->get_id(), 'product_cat' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return false;
	}

	foreach ( $terms as $term ) {
		if ( in_array( $term->slug, $quote_cats, true ) ) {
			return true;
		}
		$ancestor = $term;
		while ( $ancestor->parent ) {
			$ancestor = get_term( $ancestor->parent, 'product_cat' );
			if ( $ancestor && ! is_wp_error( $ancestor ) && in_array( $ancestor->slug, $quote_cats, true ) ) {
				return true;
			}
		}
	}

	return false;
}

function jcs_quote_only_not_purchasable( bool $purchasable, $product ): bool {
	if ( jcs_is_quote_only_product( $product ) ) {
		return false;
	}
	return $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'jcs_quote_only_not_purchasable', 10, 2 );

function jcs_quote_button_text( string $text, $product ): string {
	if ( jcs_is_quote_only_product( $product ) ) {
		return __( 'Request a Quote', 'sunrooflighting' );
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'jcs_quote_button_text', 10, 2 );
add_filter( 'woocommerce_product_single_add_to_cart_text', 'jcs_quote_button_text', 10, 2 );

add_filter( 'woocommerce_loop_add_to_cart_link', 'jcs_replace_add_to_cart_link', 10, 2 );

function jcs_replace_add_to_cart_link( string $html, $product ): string {
	if ( ! jcs_is_quote_only_product( $product ) ) {
		return $html;
	}

	$url  = add_query_arg( 'package', urlencode( $product->get_name() ), home_url( '/quote/' ) );
	$html = sprintf(
		'<a href="%s" class="button jcs-btn-quote product_type_simple">%s</a>',
		esc_url( $url ),
		esc_html__( 'Request a Quote', 'sunrooflighting' )
	);

	return $html;
}

function jcs_single_product_quote_button(): void {
	global $product;
	if ( ! jcs_is_quote_only_product( $product ) ) {
		return;
	}

	$url = add_query_arg( 'package', urlencode( $product->get_name() ), home_url( '/quote/' ) );
	echo '<a href="' . esc_url( $url ) . '" class="single_add_to_cart_button button alt jcs-btn-quote">' . esc_html__( 'Request a Quote', 'sunrooflighting' ) . '</a>';
}
add_action( 'woocommerce_single_product_summary', 'jcs_single_product_quote_button', 30 );

function jcs_hide_add_to_cart_for_packages(): void {
	global $product;
	if ( jcs_is_quote_only_product( $product ) ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	}
}
add_action( 'woocommerce_before_single_product', 'jcs_hide_add_to_cart_for_packages' );

/**
 * Monthly financing estimate on package product pages.
 */
function jcs_display_financing_estimate(): void {
	global $product;
	if ( ! $product || ! jcs_is_quote_only_product( $product ) ) {
		return;
	}

	$price = (float) $product->get_price();
	if ( $price <= 0 ) {
		return;
	}

	$monthly = round( $price / 240, 0 );
	?>
	<div class="jcs-financing-estimate">
		<strong><?php esc_html_e( 'Financing Available', 'sunrooflighting' ); ?></strong>
		<p>
			<?php
			printf(
				/* translators: 1: monthly payment, 2: term years */
				esc_html__( 'As low as $%1$s/mo over 20 years. Subject to credit approval.', 'sunrooflighting' ),
				esc_html( number_format( $monthly ) )
			);
			?>
		</p>
		<a href="<?php echo esc_url( home_url( '/financing/' ) ); ?>"><?php esc_html_e( 'Learn about financing', 'sunrooflighting' ); ?> →</a>
	</div>
	<?php
}
add_action( 'woocommerce_single_product_summary', 'jcs_display_financing_estimate', 25 );

function jcs_widgets_init(): void {
	register_sidebar(
		array(
			'name'          => __( 'Product Archive Filters', 'sunrooflighting' ),
			'id'            => 'product-archive',
			'description'   => __( 'Sidebar filters on product category pages.', 'sunrooflighting' ),
			'before_widget' => '<div id="%1$s" class="jcs-filter-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="jcs-filter-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'jcs_widgets_init' );

function jcs_register_default_widgets(): void {
	if ( get_option( 'jcs_widgets_registered' ) ) {
		return;
	}

	$sidebars_widgets = get_option( 'sidebars_widgets', array() );
	$sidebars_widgets['product-archive'] = array(
		'woocommerce_layered_nav-1',
		'woocommerce_layered_nav-2',
		'woocommerce_price_filter-1',
	);

	update_option( 'sidebars_widgets', $sidebars_widgets );

	update_option(
		'widget_woocommerce_layered_nav',
		array(
			1 => array(
				'title'        => 'Categories',
				'taxonomy'     => 'product_cat',
				'display_type' => 'list',
				'query_type'   => 'or',
			),
			2 => array(
				'title'        => 'Brand',
				'taxonomy'     => 'pa_brand',
				'display_type' => 'list',
				'query_type'   => 'or',
			),
			'_multiwidget' => 1,
		)
	);

	update_option(
		'widget_woocommerce_price_filter',
		array(
			1 => array( 'title' => 'Price' ),
			'_multiwidget' => 1,
		)
	);

	update_option( 'jcs_widgets_registered', true );
}
add_action( 'after_switch_theme', 'jcs_register_default_widgets' );

function jcs_get_departments(): array {
	return get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);
}

function jcs_get_top_categories(): array {
	if ( ! is_product_category() ) {
		return array();
	}

	$current = get_queried_object();
	if ( ! $current ) {
		return array();
	}

	$parent_id = $current->parent ? $current->parent : $current->term_id;

	return get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $parent_id,
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);
}

function jcs_category_icon( string $slug ): string {
	$icons = array(
		'installation-packages' => '☀️',
		'residential-packages'  => '🏠',
		'commercial-packages'   => '🏢',
		'solar-panels'          => '🔆',
		'monocrystalline'       => '🔆',
		'polycrystalline'       => '💡',
		'inverters'             => '⚡',
		'string-inverters'      => '⚡',
		'microinverters'        => '🔌',
		'batteries-storage'     => '🔋',
		'home-batteries'        => '🔋',
		'accessories'           => '🔧',
		'mounting-hardware'     => '📐',
		'monitoring'            => '📊',
	);

	return $icons[ $slug ] ?? '☀️';
}

function jcs_get_template_part( string $slug, array $args = array() ): void {
	$path = JCS_DIR . '/template-parts/' . $slug . '.php';
	if ( file_exists( $path ) ) {
		if ( ! empty( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}
		include $path;
	}
}

function jcs_archive_title(): string {
	return jcs_archive_heading();
}

function jcs_archive_heading(): string {
	if ( is_product_category() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->name ) ) {
			return $term->name;
		}
	}

	if ( is_shop() ) {
		return __( 'Solar Equipment & Installation Packages', 'sunrooflighting' );
	}

	return __( 'Products', 'sunrooflighting' );
}

function jcs_archive_count_label(): string {
	global $wp_query;

	$count = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;

	if ( is_product_category() ) {
		return sprintf(
			/* translators: %d: number of products */
			_n( '%d product found', '%d products found', $count, 'sunrooflighting' ),
			$count
		);
	}

	return sprintf(
		/* translators: %d: number of products */
		_n( '%d item found', '%d items found', $count, 'sunrooflighting' ),
		$count
	);
}

function jcs_woocommerce_template_path(): string {
	return 'woocommerce/';
}
add_filter( 'woocommerce_template_path', 'jcs_woocommerce_template_path' );

function jcs_get_package_products(): array {
	return wc_get_products(
		array(
			'category' => array( 'residential-packages', 'commercial-packages' ),
			'limit'    => 8,
			'status'   => 'publish',
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
		)
	);
}

function jcs_quote_url( string $package = '' ): string {
	$url = home_url( '/quote/' );
	if ( $package ) {
		$url = add_query_arg( 'package', urlencode( $package ), $url );
	}
	return $url;
}

function jcs_term_link( string $slug ): string {
	$link = get_term_link( $slug, 'product_cat' );
	return is_wp_error( $link ) ? home_url( '/shop/' ) : $link;
}
