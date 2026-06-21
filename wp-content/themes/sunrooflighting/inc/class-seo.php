<?php
/**
 * SEO meta tags, Open Graph, Twitter Cards, and JSON-LD structured data.
 *
 * @package Sunrooflighting
 */

defined( 'ABSPATH' ) || exit;

class JCS_SEO {

	private const META_KEY = '_jcs_meta_description';

	public static function init(): void {
		add_action( 'wp_head', array( __CLASS__, 'render_head_tags' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'output_json_ld' ), 20 );
		add_filter( 'document_title_parts', array( __CLASS__, 'filter_document_title' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_meta_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_meta_box' ) );
	}

	/**
	 * Output all SEO head tags in one pass (called from wp_head and header.php fallback).
	 */
	public static function render_head_tags(): void {
		static $rendered = false;
		if ( $rendered ) {
			return;
		}
		$rendered = true;

		self::output_meta_tags();
		self::output_canonical();
		self::output_open_graph();
		self::output_twitter_cards();
	}

	public static function site_name(): string {
		return get_bloginfo( 'name' ) ?: 'Sunrooflighting';
	}

	public static function default_description(): string {
		return __( 'Sunrooflighting provides professional residential and commercial solar panel installation, savings calculators, flexible financing, and free quotes across Arizona.', 'sunrooflighting' );
	}

	public static function default_keywords(): string {
		return 'solar installation, solar panels, residential solar, commercial solar, solar financing, solar calculator, Sunrooflighting, Arizona solar';
	}

	/**
	 * @return array{title: string, description: string, url: string, type: string, image: string, keywords: string}
	 */
	public static function get_context(): array {
		$context = array(
			'title'       => wp_get_document_title(),
			'description' => self::default_description(),
			'url'         => self::current_url(),
			'type'        => 'website',
			'image'       => self::default_image(),
			'keywords'    => self::default_keywords(),
		);

		if ( self::is_site_home() ) {
			$context['title']       = self::site_name() . ' | ' . __( 'Solar Panel Installation & Free Quotes', 'sunrooflighting' );
			$context['description'] = self::default_description();
			$context['type']        = 'website';
			$context['url']         = home_url( '/' );

			$front_id = (int) get_option( 'page_on_front' );
			if ( $front_id ) {
				$custom = get_post_meta( $front_id, self::META_KEY, true );
				if ( $custom ) {
					$context['description'] = $custom;
				} else {
					$excerpt = get_post_field( 'post_excerpt', $front_id );
					if ( $excerpt ) {
						$context['description'] = wp_strip_all_tags( $excerpt );
					}
				}
			}
		} elseif ( is_singular() ) {
			$post = get_queried_object();
			if ( $post instanceof WP_Post ) {
				$custom = get_post_meta( $post->ID, self::META_KEY, true );
				$context['title']       = get_the_title( $post ) . ' | ' . self::site_name();
				$context['description'] = $custom ?: self::excerpt_from_post( $post );
				$context['url']           = get_permalink( $post );
				$context['type']          = is_singular( 'product' ) ? 'product' : 'article';
				$context['image']         = self::image_for_post( $post ) ?: $context['image'];
				$context['keywords']      = self::keywords_for_post( $post );
			}
		} elseif ( is_product_category() || is_product_tag() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$context['title']       = $term->name . ' | ' . self::site_name();
				$context['description'] = $term->description
					? wp_strip_all_tags( $term->description )
					: sprintf(
						/* translators: %s: category name */
						__( 'Browse %s from Sunrooflighting. Request a quote for professional solar installation.', 'sunrooflighting' ),
						$term->name
					);
				$context['url']      = get_term_link( $term );
				$context['type']     = 'website';
				$context['keywords'] = self::keywords_for_term( $term );
			}
		} elseif ( is_shop() ) {
			$context['title']       = __( 'Solar Equipment & Installation Packages', 'sunrooflighting' ) . ' | ' . self::site_name();
			$context['description'] = __( 'Shop solar panels, inverters, batteries, and turnkey installation packages. Credit card and financing available.', 'sunrooflighting' );
			$context['url']         = wc_get_page_permalink( 'shop' );
		} elseif ( is_search() ) {
			$context['title']       = sprintf(
				/* translators: %s: search query */
				__( 'Search results for "%s"', 'sunrooflighting' ),
				get_search_query()
			) . ' | ' . self::site_name();
			$context['description'] = sprintf(
				/* translators: %s: search query */
				__( 'Search results for "%s" on Sunrooflighting.', 'sunrooflighting' ),
				get_search_query()
			);
		}

		$context['description'] = self::trim_description( $context['description'] );
		$context['title']       = wp_strip_all_tags( $context['title'] );

		return $context;
	}

	public static function filter_document_title( array $title ): array {
		if ( self::is_site_home() ) {
			$title['title'] = self::site_name();
			$title['tagline'] = __( 'Solar Panel Installation & Free Quotes', 'sunrooflighting' );
		} elseif ( is_page_template( 'page-calculator.php' ) ) {
			$title['title'] = __( 'Solar Savings Calculator', 'sunrooflighting' );
		} elseif ( is_page_template( 'page-quote.php' ) ) {
			$title['title'] = __( 'Get a Free Solar Quote', 'sunrooflighting' );
		} elseif ( is_page_template( 'page-financing.php' ) ) {
			$title['title'] = __( 'Solar Financing Options', 'sunrooflighting' );
		} elseif ( is_shop() ) {
			$title['title'] = __( 'Solar Equipment & Packages', 'sunrooflighting' );
		}

		return $title;
	}

	public static function output_meta_tags(): void {
		$ctx = self::get_context();
		?>
		<meta name="description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
		<meta name="keywords" content="<?php echo esc_attr( $ctx['keywords'] ); ?>">
		<meta name="author" content="<?php echo esc_attr( self::site_name() ); ?>">
		<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
		<meta name="theme-color" content="#1c83c6">
		<?php
	}

	public static function output_open_graph(): void {
		$ctx = self::get_context();
		?>
		<meta property="og:locale" content="<?php echo esc_attr( str_replace( '-', '_', get_locale() ) ); ?>">
		<meta property="og:site_name" content="<?php echo esc_attr( self::site_name() ); ?>">
		<meta property="og:title" content="<?php echo esc_attr( $ctx['title'] ); ?>">
		<meta property="og:description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
		<meta property="og:url" content="<?php echo esc_url( $ctx['url'] ); ?>">
		<meta property="og:type" content="<?php echo esc_attr( $ctx['type'] ); ?>">
		<?php if ( self::is_valid_image_url( $ctx['image'] ) ) : ?>
			<meta property="og:image" content="<?php echo esc_url( $ctx['image'] ); ?>">
			<meta property="og:image:width" content="1200">
			<meta property="og:image:height" content="630">
			<meta property="og:image:alt" content="<?php echo esc_attr( $ctx['title'] ); ?>">
		<?php endif; ?>
		<?php
	}

	public static function output_twitter_cards(): void {
		$ctx     = self::get_context();
		$has_img = self::is_valid_image_url( $ctx['image'] );
		?>
		<meta name="twitter:card" content="<?php echo $has_img ? 'summary_large_image' : 'summary'; ?>">
		<meta name="twitter:title" content="<?php echo esc_attr( $ctx['title'] ); ?>">
		<meta name="twitter:description" content="<?php echo esc_attr( $ctx['description'] ); ?>">
		<?php if ( $has_img ) : ?>
			<meta name="twitter:image" content="<?php echo esc_url( $ctx['image'] ); ?>">
		<?php endif; ?>
		<?php
	}

	public static function output_canonical(): void {
		$ctx = self::get_context();
		if ( ! empty( $ctx['url'] ) && ! is_wp_error( $ctx['url'] ) ) {
			echo '<link rel="canonical" href="' . esc_url( $ctx['url'] ) . '">' . "\n";
		}
	}

	public static function output_json_ld(): void {
		$schemas = array(
			self::organization_schema(),
			self::website_schema(),
		);

		if ( self::is_site_home() ) {
			$schemas[] = self::local_business_schema();
		}

		if ( is_singular( 'product' ) ) {
			$product_schema = self::product_schema();
			if ( $product_schema ) {
				$schemas[] = $product_schema;
			}
		}

		$breadcrumb = self::breadcrumb_schema();
		if ( $breadcrumb ) {
			$schemas[] = $breadcrumb;
		}

		foreach ( $schemas as $schema ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
		}
	}

	private static function organization_schema(): array {
		return array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Organization',
			'name'        => self::site_name(),
			'url'         => home_url( '/' ),
			'description' => self::default_description(),
			'logo'        => self::default_image(),
			'contactPoint' => array(
				'@type'             => 'ContactPoint',
				'contactType'       => 'sales',
				'url'               => home_url( '/quote/' ),
				'availableLanguage' => 'English',
			),
		);
	}

	private static function website_schema(): array {
		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => self::site_name(),
			'url'             => home_url( '/' ),
			'description'     => self::default_description(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}&post_type=product' ),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	private static function local_business_schema(): array {
		return array(
			'@context'    => 'https://schema.org',
			'@type'       => 'LocalBusiness',
			'name'        => self::site_name(),
			'url'         => home_url( '/' ),
			'description' => self::default_description(),
			'priceRange'  => '$$',
			'address'     => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => '500 Solar Way',
				'addressLocality' => 'Phoenix',
				'addressRegion'   => 'AZ',
				'postalCode'      => '85001',
				'addressCountry'  => 'US',
			),
			'areaServed'  => array(
				'@type' => 'State',
				'name'  => 'Arizona',
			),
		);
	}

	private static function product_schema(): ?array {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return null;
		}

		$schema = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'Product',
			'name'        => $product->get_name(),
			'description' => self::trim_description( $product->get_short_description() ?: $product->get_description() ),
			'url'         => get_permalink( $product->get_id() ),
			'sku'         => $product->get_sku() ?: (string) $product->get_id(),
			'brand'       => array(
				'@type' => 'Brand',
				'name'  => self::site_name(),
			),
		);

		$image = wp_get_attachment_url( $product->get_image_id() );
		if ( $image ) {
			$schema['image'] = $image;
		}

		if ( $product->get_price() ) {
			$schema['offers'] = array(
				'@type'         => 'Offer',
				'price'         => $product->get_price(),
				'priceCurrency' => get_woocommerce_currency(),
				'availability'  => $product->is_in_stock()
					? 'https://schema.org/InStock'
					: 'https://schema.org/OutOfStock',
				'url'           => get_permalink( $product->get_id() ),
			);
		}

		if ( $product->get_average_rating() > 0 ) {
			$schema['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $product->get_average_rating(),
				'reviewCount' => $product->get_review_count(),
			);
		}

		return $schema;
	}

	private static function breadcrumb_schema(): ?array {
		$items = array();
		$pos   = 1;

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => __( 'Home', 'sunrooflighting' ),
			'item'     => home_url( '/' ),
		);

		if ( is_product() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => __( 'Shop', 'sunrooflighting' ),
				'item'     => wc_get_page_permalink( 'shop' ),
			);
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		} elseif ( is_product_category() ) {
			$term = get_queried_object();
			if ( $term instanceof WP_Term ) {
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => __( 'Shop', 'sunrooflighting' ),
					'item'     => wc_get_page_permalink( 'shop' ),
				);
				$items[] = array(
					'@type'    => 'ListItem',
					'position' => $pos++,
					'name'     => $term->name,
					'item'     => get_term_link( $term ),
				);
			}
		} elseif ( is_page() && ! is_front_page() ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $pos++,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		} else {
			return null;
		}

		return array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}

	public static function register_meta_box(): void {
		add_meta_box(
			'jcs-seo-meta',
			__( 'SEO Description', 'sunrooflighting' ),
			array( __CLASS__, 'render_meta_box' ),
			array( 'page', 'post', 'product' ),
			'normal',
			'low'
		);
	}

	public static function render_meta_box( WP_Post $post ): void {
		wp_nonce_field( 'jcs_save_seo_meta', 'jcs_seo_nonce' );
		$value = get_post_meta( $post->ID, self::META_KEY, true );
		?>
		<p>
			<label for="jcs_meta_description"><?php esc_html_e( 'Custom meta description (optional, max 160 characters recommended):', 'sunrooflighting' ); ?></label>
			<textarea id="jcs_meta_description" name="jcs_meta_description" rows="3" style="width:100%;"><?php echo esc_textarea( $value ); ?></textarea>
		</p>
		<?php
	}

	public static function save_meta_box( int $post_id ): void {
		if ( ! isset( $_POST['jcs_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['jcs_seo_nonce'] ) ), 'jcs_save_seo_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$desc = isset( $_POST['jcs_meta_description'] )
			? sanitize_textarea_field( wp_unslash( $_POST['jcs_meta_description'] ) )
			: '';
		update_post_meta( $post_id, self::META_KEY, $desc );
	}

	private static function excerpt_from_post( WP_Post $post ): string {
		if ( $post->post_excerpt ) {
			return wp_strip_all_tags( $post->post_excerpt );
		}
		if ( $post->post_content ) {
			return wp_strip_all_tags( wp_trim_words( $post->post_content, 30, '…' ) );
		}
		return self::default_description();
	}

	private static function image_for_post( WP_Post $post ): string {
		if ( has_post_thumbnail( $post ) ) {
			$url = get_the_post_thumbnail_url( $post, 'large' );
			if ( $url ) {
				return $url;
			}
		}
		if ( 'product' === $post->post_type && function_exists( 'wc_get_product' ) ) {
			$product = wc_get_product( $post->ID );
			if ( $product ) {
				$url = wp_get_attachment_url( $product->get_image_id() );
				if ( $url ) {
					return $url;
				}
			}
		}
		return '';
	}

	private static function default_image(): string {
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
			if ( $url ) {
				return $url;
			}
		}
		$site_icon = get_site_icon_url( 512 );
		if ( $site_icon ) {
			return $site_icon;
		}
		$local = JCS_DIR . '/assets/images/og-default.jpg';
		if ( file_exists( $local ) ) {
			return JCS_URI . '/assets/images/og-default.jpg';
		}

		return '';
	}

	/**
	 * Whether the current request is the site homepage.
	 */
	private static function is_site_home(): bool {
		if ( is_front_page() ) {
			return true;
		}

		global $wp;
		$request = isset( $wp->request ) ? trim( (string) $wp->request, '/' ) : '';

		return '' === $request && ! is_admin() && ! is_feed() && ! is_search();
	}

	/**
	 * OG/Twitter require a real image URL, not a page URL.
	 */
	private static function is_valid_image_url( string $url ): bool {
		if ( empty( $url ) ) {
			return false;
		}

		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $path ) {
			return false;
		}

		$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

		return in_array( $ext, array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ), true );
	}

	private static function keywords_for_post( WP_Post $post ): string {
		$map = array(
			'calculator' => 'solar savings calculator, utility bill upload, solar estimate, energy savings',
			'quote'      => 'solar quote, free solar estimate, solar installation quote, contact solar installer',
			'financing'  => 'solar financing, solar loans, monthly solar payments, solar credit card',
		);
		if ( isset( $map[ $post->post_name ] ) ) {
			return $map[ $post->post_name ];
		}
		if ( 'product' === $post->post_type ) {
			return 'solar equipment, ' . sanitize_title( $post->post_title ) . ', Sunrooflighting';
		}
		return self::default_keywords();
	}

	private static function keywords_for_term( WP_Term $term ): string {
		$map = array(
			'installation-packages' => 'solar installation packages, residential solar, commercial solar',
			'residential-packages'  => 'home solar packages, residential solar installation',
			'commercial-packages'   => 'commercial solar installation, business solar packages',
			'solar-panels'          => 'solar panels, monocrystalline panels, photovoltaic modules',
			'inverters'             => 'solar inverters, microinverters, string inverters',
			'batteries-storage'     => 'solar batteries, home energy storage, battery backup',
		);
		return $map[ $term->slug ] ?? $term->name . ', solar, Sunrooflighting';
	}

	private static function trim_description( string $text ): string {
		$text = wp_strip_all_tags( $text );
		$text = preg_replace( '/\s+/', ' ', $text );
		if ( mb_strlen( $text ) > 160 ) {
			$text = mb_substr( $text, 0, 157 ) . '…';
		}
		return trim( $text );
	}

	private static function current_url(): string {
		if ( is_singular() ) {
			return get_permalink() ?: home_url( '/' );
		}
		if ( is_product_category() || is_product_tag() ) {
			$link = get_term_link( get_queried_object() );
			return is_wp_error( $link ) ? home_url( '/' ) : $link;
		}
		if ( is_shop() ) {
			return wc_get_page_permalink( 'shop' ) ?: home_url( '/shop/' );
		}
		global $wp;
		return home_url( add_query_arg( array(), $wp->request ?? '' ) );
	}

	/**
	 * Extra SEO copy blocks for product category archives.
	 */
	public static function category_seo_content( WP_Term $term ): string {
		$blocks = array(
			'installation-packages' => array(
				'heading' => __( 'Turnkey Solar Installation Packages', 'sunrooflighting' ),
				'body'    => __( 'Sunrooflighting installation packages include system design, premium equipment, permitting, professional installation, and grid interconnection. Every package is quote-based so we can size the system to your roof, usage, and budget.', 'sunrooflighting' ),
			),
			'residential-packages' => array(
				'heading' => __( 'Residential Solar Packages for Every Home Size', 'sunrooflighting' ),
				'body'    => __( 'From starter 4 kW systems to whole-home 16 kW installations with battery backup, our residential packages are designed to maximize savings and reliability. Request a free quote to get a custom proposal.', 'sunrooflighting' ),
			),
			'commercial-packages' => array(
				'heading' => __( 'Commercial Solar Solutions', 'sunrooflighting' ),
				'body'    => __( 'Reduce operating costs with commercial solar from 20 kW to 100 kW+. We provide structural assessment, demand monitoring, and dedicated project management for businesses across Arizona.', 'sunrooflighting' ),
			),
			'solar-panels' => array(
				'heading' => __( 'High-Efficiency Solar Panels', 'sunrooflighting' ),
				'body'    => __( 'Shop monocrystalline and polycrystalline solar panels from leading brands. All panels include manufacturer warranties and are installed by Sunrooflighting certified technicians.', 'sunrooflighting' ),
			),
			'inverters' => array(
				'heading' => __( 'Solar Inverters & Power Conversion', 'sunrooflighting' ),
				'body'    => __( 'Choose string inverters for cost-effective installations or microinverters for panel-level monitoring and maximum production. We help match the right inverter to your system design.', 'sunrooflighting' ),
			),
			'batteries-storage' => array(
				'heading' => __( 'Home Battery & Energy Storage', 'sunrooflighting' ),
				'body'    => __( 'Store excess solar energy for evening use and backup power during outages. Pair battery storage with any installation package for greater energy independence.', 'sunrooflighting' ),
			),
		);

		if ( ! isset( $blocks[ $term->slug ] ) ) {
			return '';
		}

		$block = $blocks[ $term->slug ];
		return sprintf(
			'<h2>%s</h2><p>%s</p>',
			esc_html( $block['heading'] ),
			esc_html( $block['body'] )
		);
	}
}
