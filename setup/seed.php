<?php
/**
 * Seed demo categories and products for Sunrooflighting.
 *
 * Run: lando wp eval-file setup/seed.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function jcs_get_or_create_category( string $name, string $slug, int $parent = 0, string $description = '' ): int {
	$existing = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $existing ) {
		return (int) $existing->term_id;
	}

	$result = wp_insert_term(
		$name,
		'product_cat',
		array(
			'slug'        => $slug,
			'parent'      => $parent,
			'description' => $description,
		)
	);

	if ( is_wp_error( $result ) ) {
		WP_CLI::warning( "Could not create category {$name}: " . $result->get_error_message() );
		return 0;
	}

	return (int) $result['term_id'];
}

function jcs_category_tree(): array {
	return array(
		'Installation Packages' => array(
			'slug'        => 'installation-packages',
			'description' => 'Complete solar installation packages for homes and businesses. Request a personalized quote for your property.',
			'children'    => array(
				'Residential' => array(
					'slug'        => 'residential-packages',
					'description' => 'Turnkey residential solar packages sized for your home energy needs.',
					'children'    => array(),
				),
				'Commercial' => array(
					'slug'        => 'commercial-packages',
					'description' => 'Commercial solar solutions for offices, warehouses, and retail spaces.',
					'children'    => array(),
				),
			),
		),
		'Solar Panels' => array(
			'slug'        => 'solar-panels',
			'description' => 'High-efficiency monocrystalline and polycrystalline solar panels.',
			'children'    => array(
				'Monocrystalline' => array( 'slug' => 'monocrystalline', 'children' => array() ),
				'Polycrystalline' => array( 'slug' => 'polycrystalline', 'children' => array() ),
			),
		),
		'Inverters' => array(
			'slug'        => 'inverters',
			'description' => 'String inverters and microinverters for optimal energy conversion.',
			'children'    => array(
				'String Inverters'   => array( 'slug' => 'string-inverters', 'children' => array() ),
				'Microinverters'     => array( 'slug' => 'microinverters', 'children' => array() ),
			),
		),
		'Batteries & Storage' => array(
			'slug'        => 'batteries-storage',
			'description' => 'Home battery backup and energy storage solutions.',
			'children'    => array(
				'Home Batteries' => array( 'slug' => 'home-batteries', 'children' => array() ),
			),
		),
		'Accessories' => array(
			'slug'        => 'accessories',
			'description' => 'Mounting hardware, monitoring systems, and solar accessories.',
			'children'    => array(
				'Mounting Hardware' => array( 'slug' => 'mounting-hardware', 'children' => array() ),
				'Monitoring'          => array( 'slug' => 'monitoring', 'children' => array() ),
			),
		),
	);
}

function jcs_seed_categories(): array {
	$map  = array();
	$tree = jcs_category_tree();

	foreach ( $tree as $dept_name => $dept_data ) {
		$dept_id = jcs_get_or_create_category(
			$dept_name,
			$dept_data['slug'],
			0,
			$dept_data['description'] ?? ''
		);
		$map[ $dept_data['slug'] ] = $dept_id;

		foreach ( $dept_data['children'] as $cat_name => $cat_data ) {
			if ( is_string( $cat_data ) ) {
				continue;
			}

			$cat_slug = $cat_data['slug'] ?? sanitize_title( $cat_name );
			$cat_id   = jcs_get_or_create_category(
				$cat_name,
				$cat_slug,
				$dept_id,
				$cat_data['description'] ?? ''
			);
			$map[ $cat_slug ] = $cat_id;

			foreach ( $cat_data['children'] ?? array() as $sub_name => $sub_slug ) {
				if ( is_array( $sub_slug ) ) {
					$sub_slug = $sub_slug['slug'] ?? sanitize_title( $sub_name );
				}
				$sub_id           = jcs_get_or_create_category( $sub_name, $sub_slug, $cat_id );
				$map[ $sub_slug ] = $sub_id;
			}
		}
	}

	return $map;
}

function jcs_seed_brand_attribute(): void {
	if ( ! function_exists( 'wc_create_attribute' ) ) {
		return;
	}

	$existing = wc_get_attribute_taxonomies();
	foreach ( $existing as $attr ) {
		if ( 'brand' === $attr->attribute_name ) {
			return;
		}
	}

	wc_create_attribute(
		array(
			'name'         => 'Brand',
			'slug'         => 'brand',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => true,
		)
	);

	register_taxonomy(
		'pa_brand',
		apply_filters( 'woocommerce_taxonomy_objects_pa_brand', array( 'product' ) ),
		apply_filters(
			'woocommerce_taxonomy_args_pa_brand',
			array(
				'labels'       => array( 'name' => 'Brand' ),
				'hierarchical' => false,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => 'brand' ),
			)
		)
	);
}

function jcs_product_catalog(): array {
	$brands = array( 'SunPower', 'LG Solar', 'REC', 'Enphase', 'Tesla', 'Canadian Solar', 'Qcells', 'Panasonic' );

	$templates = array(
		// Installation packages (quote-only)
		array(
			'name'        => 'Starter Package — 4 kW Residential',
			'cat'         => 'residential-packages',
			'price'       => 12000,
			'description' => 'Ideal for small homes and condos. Includes 10 high-efficiency panels, inverter, mounting, permits, and professional installation. Estimated annual production: 5,800 kWh.',
			'meta'        => array( 'system_kw' => 4, 'panels' => 10, 'annual_kwh' => 5800 ),
		),
		array(
			'name'        => 'Family Package — 8 kW Residential',
			'cat'         => 'residential-packages',
			'price'       => 22000,
			'description' => 'Our most popular package for average-sized homes. 20 panels, string inverter, monitoring, and full installation. Estimated annual production: 11,600 kWh.',
			'meta'        => array( 'system_kw' => 8, 'panels' => 20, 'annual_kwh' => 11600 ),
		),
		array(
			'name'        => 'Whole-Home Package — 12 kW Residential',
			'cat'         => 'residential-packages',
			'price'       => 32000,
			'description' => 'Power your entire home with 30 premium panels, microinverters, battery-ready wiring, and extended warranty. Estimated annual production: 17,400 kWh.',
			'meta'        => array( 'system_kw' => 12, 'panels' => 30, 'annual_kwh' => 17400 ),
		),
		array(
			'name'        => 'Premium Package — 16 kW + Battery',
			'cat'         => 'residential-packages',
			'price'       => 45000,
			'description' => 'Maximum independence with 40 panels plus 13.5 kWh home battery storage. Includes backup power and smart energy management.',
			'meta'        => array( 'system_kw' => 16, 'panels' => 40, 'annual_kwh' => 23200, 'battery_kwh' => 13.5 ),
		),
		array(
			'name'        => 'Commercial Starter — 20 kW',
			'cat'         => 'commercial-packages',
			'price'       => 48000,
			'description' => 'Entry-level commercial installation for small offices and retail. 50 panels, commercial-grade inverter, and structural assessment.',
			'meta'        => array( 'system_kw' => 20, 'panels' => 50, 'annual_kwh' => 29000 ),
		),
		array(
			'name'        => 'Commercial Pro — 50 kW',
			'cat'         => 'commercial-packages',
			'price'       => 110000,
			'description' => 'Mid-size commercial system for warehouses and multi-tenant buildings. Includes demand monitoring and maintenance plan.',
			'meta'        => array( 'system_kw' => 50, 'panels' => 125, 'annual_kwh' => 72500 ),
		),
		array(
			'name'        => 'Commercial Enterprise — 100 kW',
			'cat'         => 'commercial-packages',
			'price'       => 200000,
			'description' => 'Large-scale commercial solar with custom engineering, 25-year performance guarantee, and dedicated project manager.',
			'meta'        => array( 'system_kw' => 100, 'panels' => 250, 'annual_kwh' => 145000 ),
		),

		// Solar panels
		array( 'name' => 'SunPower Maxeon 6 AC 420W Monocrystalline Panel', 'cat' => 'monocrystalline', 'price' => 349.99, 'brand' => 'SunPower' ),
		array( 'name' => 'LG NeON R 405W High-Efficiency Panel', 'cat' => 'monocrystalline', 'price' => 299.99, 'brand' => 'LG Solar' ),
		array( 'name' => 'REC Alpha Pure 410W Panel', 'cat' => 'monocrystalline', 'price' => 279.99, 'brand' => 'REC' ),
		array( 'name' => 'Canadian Solar HiKu7 645W Bifacial Panel', 'cat' => 'polycrystalline', 'price' => 249.99, 'brand' => 'Canadian Solar' ),
		array( 'name' => 'Qcells Q.PEAK DUO BLK 400W Panel', 'cat' => 'monocrystalline', 'price' => 259.99, 'brand' => 'Qcells' ),

		// Inverters
		array( 'name' => 'Enphase IQ8+ Microinverter', 'cat' => 'microinverters', 'price' => 189.99, 'brand' => 'Enphase' ),
		array( 'name' => 'Enphase IQ8M Microinverter', 'cat' => 'microinverters', 'price' => 169.99, 'brand' => 'Enphase' ),
		array( 'name' => 'SolarEdge HD-Wave 7.6kW String Inverter', 'cat' => 'string-inverters', 'price' => 1599.99, 'brand' => 'SolarEdge' ),
		array( 'name' => 'Fronius Primo 5.0 String Inverter', 'cat' => 'string-inverters', 'price' => 1299.99, 'brand' => 'Fronius' ),

		// Batteries
		array( 'name' => 'Tesla Powerwall 3 — 13.5 kWh Home Battery', 'cat' => 'home-batteries', 'price' => 8999.99, 'brand' => 'Tesla' ),
		array( 'name' => 'Enphase IQ Battery 5P — 5 kWh', 'cat' => 'home-batteries', 'price' => 4999.99, 'brand' => 'Enphase' ),
		array( 'name' => 'LG RESU10H Prime — 9.6 kWh', 'cat' => 'home-batteries', 'price' => 7499.99, 'brand' => 'LG Solar' ),

		// Accessories
		array( 'name' => 'IronRidge XR100 Rail Mounting System (per panel)', 'cat' => 'mounting-hardware', 'price' => 89.99 ),
		array( 'name' => 'SolarEdge Home Energy Monitor', 'cat' => 'monitoring', 'price' => 349.99, 'brand' => 'SolarEdge' ),
		array( 'name' => 'Enphase Enlighten Monitoring Kit', 'cat' => 'monitoring', 'price' => 249.99, 'brand' => 'Enphase' ),
	);

	foreach ( $templates as $i => &$product ) {
		if ( empty( $product['brand'] ) ) {
			$product['brand'] = $brands[ $i % count( $brands ) ];
		}
	}

	return $templates;
}

function jcs_attach_placeholder_image( int $product_id, int $index ): void {
	$seed = 200 + $index;
	$url  = "https://picsum.photos/seed/sun{$seed}/600/600";
	$tmp  = download_url( $url );

	if ( is_wp_error( $tmp ) ) {
		return;
	}

	$file_array = array(
		'name'     => "product-{$product_id}.jpg",
		'tmp_name' => $tmp,
	);

	$attachment_id = media_handle_sideload( $file_array, $product_id );

	if ( ! is_wp_error( $attachment_id ) ) {
		set_post_thumbnail( $product_id, $attachment_id );
	}

	@unlink( $tmp );
}

function jcs_seed_products( array $category_map ): void {
	$catalog = jcs_product_catalog();
	$created = 0;
	$skipped = 0;

	foreach ( $catalog as $index => $item ) {
		$slug = sanitize_title( $item['name'] );

		$existing = get_page_by_path( $slug, OBJECT, 'product' );
		if ( $existing ) {
			++$skipped;
			continue;
		}

		$product = new WC_Product_Simple();
		$product->set_name( $item['name'] );
		$product->set_slug( $slug );
		$product->set_regular_price( (string) $item['price'] );

		$description = $item['description'] ?? 'Quality solar equipment from Sunrooflighting. Professional-grade components backed by manufacturer warranties.';
		$product->set_description( $description );
		$product->set_short_description( 'Contact us for installation details and availability.' );
		$product->set_manage_stock( false );
		$product->set_stock_status( 'instock' );
		$product->set_catalog_visibility( 'visible' );
		$product->set_reviews_allowed( true );

		$cat_slug = $item['cat'];
		if ( isset( $category_map[ $cat_slug ] ) ) {
			$product->set_category_ids( array( $category_map[ $cat_slug ] ) );
		}

		$product_id = $product->save();

		if ( ! empty( $item['meta'] ) ) {
			foreach ( $item['meta'] as $key => $value ) {
				update_post_meta( $product_id, '_jcs_' . $key, $value );
			}
		}

		if ( ! empty( $item['brand'] ) && taxonomy_exists( 'pa_brand' ) ) {
			wp_set_object_terms( $product_id, $item['brand'], 'pa_brand' );
		}

		$rating = wp_rand( 4, 5 );
		update_post_meta( $product_id, '_wc_average_rating', (string) $rating );
		update_post_meta( $product_id, '_wc_review_count', (string) wp_rand( 3, 52 ) );

		jcs_attach_placeholder_image( $product_id, $index );
		++$created;
	}

	WP_CLI::success( "Products created: {$created}, skipped (existing): {$skipped}" );
}

WP_CLI::log( 'Seeding categories...' );
$category_map = jcs_seed_categories();
WP_CLI::success( 'Categories ready: ' . count( $category_map ) );

WP_CLI::log( 'Seeding brand attribute...' );
jcs_seed_brand_attribute();

WP_CLI::log( 'Seeding products...' );
jcs_seed_products( $category_map );

WP_CLI::success( 'Seed complete!' );
