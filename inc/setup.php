<?php
/**
 * Appearance → Bunny Babe Setup: one click to create the shop basics.
 * Safe to run more than once — it only creates what is missing.
 */
defined( 'ABSPATH' ) || exit;

add_action(
	'admin_menu',
	function () {
		add_theme_page( __( 'Bunny Babe Setup', 'bunnybabe' ), __( 'Bunny Babe Setup', 'bunnybabe' ), 'manage_options', 'bb-setup', 'bb_setup_page' );
	}
);

// Nudge to run setup after switching to the theme.
add_action(
	'admin_notices',
	function () {
		if ( ! current_user_can( 'manage_options' ) || get_option( 'bb_setup_done' ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( $screen && 'appearance_page_bb-setup' === $screen->id ) {
			return;
		}
		echo '<div class="notice notice-info"><p><strong>Bunny Babe:</strong> ' . esc_html__( 'Finish setting up your shop.', 'bunnybabe' ) . ' <a class="button button-primary" href="' . esc_url( admin_url( 'themes.php?page=bb-setup' ) ) . '">' . esc_html__( 'Open Bunny Babe Setup', 'bunnybabe' ) . '</a></p></div>';
	}
);

function bb_setup_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$log = array();
	if ( isset( $_POST['bb_run_setup'] ) && check_admin_referer( 'bb_run_setup' ) ) {
		$log = bb_run_setup();
		update_option( 'bb_setup_done', 1 );
	}
	$woo  = class_exists( 'WooCommerce' );
	$chip = false;
	$ep   = false;
	if ( $woo && function_exists( 'WC' ) && WC()->payment_gateways() ) {
		foreach ( WC()->payment_gateways()->payment_gateways() as $gw ) {
			if ( false !== stripos( $gw->id . ' ' . $gw->get_method_title(), 'chip' ) ) {
				$chip = 'yes' === $gw->enabled ? 'on' : 'installed';
			}
		}
	}
	foreach ( (array) get_option( 'active_plugins', array() ) as $p ) {
		if ( false !== stripos( $p, 'easyparcel' ) ) {
			$ep = true;
		}
	}
	$rows = array(
		array( __( 'WooCommerce active', 'bunnybabe' ), $woo ),
		array( __( 'CHIP payment plugin', 'bunnybabe' ), 'on' === $chip ? true : ( $chip ? __( 'installed — turn it on in WooCommerce → Settings → Payments', 'bunnybabe' ) : false ) ),
		array( __( 'EasyParcel plugin', 'bunnybabe' ), $ep ),
		array( __( 'Pretty links (Settings → Permalinks → Post name)', 'bunnybabe' ), (bool) get_option( 'permalink_structure' ) ),
		array( __( 'HTTPS / SSL', 'bunnybabe' ), 0 === strpos( home_url(), 'https://' ) ),
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Bunny Babe Setup', 'bunnybabe' ); ?></h1>

		<?php if ( $log ) : ?>
			<div class="notice notice-success"><p><strong><?php esc_html_e( 'Done:', 'bunnybabe' ); ?></strong></p><ul style="list-style:disc;padding-left:20px">
				<?php foreach ( $log as $line ) : ?><li><?php echo esc_html( $line ); ?></li><?php endforeach; ?>
			</ul></div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Checklist', 'bunnybabe' ); ?></h2>
		<table class="widefat striped" style="max-width:760px">
			<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<tr><td><?php echo esc_html( $r[0] ); ?></td><td><?php echo true === $r[1] ? '✅' : ( false === $r[1] ? '❌' : '⚠️ ' . esc_html( $r[1] ) ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2><?php esc_html_e( 'One-click setup', 'bunnybabe' ); ?></h2>
		<p><?php esc_html_e( 'This creates anything that is missing. It does not delete anything:', 'bunnybabe' ); ?></p>
		<ul style="list-style:disc;padding-left:20px">
			<li><?php echo esc_html( sprintf( /* translators: %s price */ __( 'The 5 scents as products (RM%s, with photos) in a "Bunny Babe" category', 'bunnybabe' ), number_format( bb_config()['unit'], 2 ) ) ); ?></li>
			<li><?php esc_html_e( 'Voucher BUNNY5 — RM5 off, first order, once per customer', 'bunnybabe' ); ?></li>
			<li><?php esc_html_e( 'A "Home" page set as your front page, and a "Track Order" page', 'bunnybabe' ); ?></li>
			<li><?php esc_html_e( 'Switches the WooCommerce Checkout page to the classic checkout (needed for this design); the old content is kept as a backup', 'bunnybabe' ); ?></li>
			<li><?php esc_html_e( 'Store: Malaysia only, currency RM', 'bunnybabe' ); ?></li>
			<li><?php echo esc_html( sprintf( /* translators: %s fee */ __( 'Shipping zone "Malaysia" with a RM%s flat rate (FREE from 2 bottles is automatic)', 'bunnybabe' ), number_format( bb_config()['ship_fee'], 2 ) ) ); ?></li>
		</ul>
		<?php if ( $woo ) : ?>
			<form method="post">
				<?php wp_nonce_field( 'bb_run_setup' ); ?>
				<p><button class="button button-primary button-hero" name="bb_run_setup" value="1"><?php esc_html_e( 'Run setup', 'bunnybabe' ); ?></button></p>
			</form>
		<?php else : ?>
			<p><strong><?php esc_html_e( 'Install and activate WooCommerce first.', 'bunnybabe' ); ?></strong></p>
		<?php endif; ?>

		<h2><?php esc_html_e( 'After setup', 'bunnybabe' ); ?></h2>
		<ol>
			<li><?php esc_html_e( 'Appearance → Customize → Bunny Babe: add your affiliate form link (and courier tracking link if you want the button).', 'bunnybabe' ); ?></li>
			<li><?php esc_html_e( 'WooCommerce → Settings → Payments: turn on CHIP and enter your keys there.', 'bunnybabe' ); ?></li>
			<li><?php esc_html_e( 'Place a test order end to end, then check Track Order with the order number and phone.', 'bunnybabe' ); ?></li>
		</ol>
	</div>
	<?php
}

/** Copy a theme image into the media library. Returns attachment ID or 0. */
function bb_sideload_asset( $file, $title ) {
	$src = BB_DIR . '/assets/img/' . $file;
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$existing = get_posts(
		array(
			'post_type'      => 'attachment',
			'meta_key'       => '_bb_asset', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $file, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		$title
	);
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_bb_asset', $file );
	return (int) $id;
}

function bb_run_setup() {
	$log = array();
	$c   = bb_config();
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array( __( 'WooCommerce is not active.', 'bunnybabe' ) );
	}

	// Store basics.
	$country = (string) get_option( 'woocommerce_default_country', '' );
	if ( 0 !== strpos( $country, 'MY' ) ) {
		update_option( 'woocommerce_default_country', 'MY:SGR' );
		$log[] = __( 'Store country set to Malaysia (Selangor — change in WooCommerce → Settings if needed).', 'bunnybabe' );
	}
	update_option( 'woocommerce_currency', 'MYR' );
	update_option( 'woocommerce_allowed_countries', 'specific' );
	update_option( 'woocommerce_specific_allowed_countries', array( 'MY' ) );
	update_option( 'woocommerce_ship_to_countries', 'specific' );
	update_option( 'woocommerce_specific_ship_to_countries', array( 'MY' ) );
	update_option( 'woocommerce_default_customer_address', 'base' );
	update_option( 'woocommerce_ship_to_destination', 'billing_only' );
	update_option( 'woocommerce_enable_coupons', 'yes' );
	$log[] = __( 'Store set to Malaysia only, currency RM, coupons on.', 'bunnybabe' );

	// Category.
	$term = term_exists( $c['category'], 'product_cat' );
	if ( ! $term ) {
		$term = wp_insert_term( 'Bunny Babe', 'product_cat', array( 'slug' => $c['category'] ) );
		$log[] = __( 'Created product category "Bunny Babe".', 'bunnybabe' );
	}
	$term_id = is_array( $term ) ? (int) $term['term_id'] : 0;

	// Products.
	foreach ( $c['scents'] as $key => $s ) {
		if ( wc_get_product_id_by_sku( $s['sku'] ) ) {
			continue;
		}
		$p = new WC_Product_Simple();
		$p->set_name( 'Bunny Babe ' . $s['name'] . ' EDP 30ml' );
		$p->set_slug( 'bunny-babe-' . $key );
		$p->set_sku( $s['sku'] );
		$p->set_regular_price( number_format( $c['unit'], 2, '.', '' ) );
		$p->set_short_description( $s['mood'] . ' — ' . implode( ', ', $s['notes'] ) );
		$p->set_description( $s['desc'] );
		$p->set_status( 'publish' );
		$p->set_catalog_visibility( 'visible' );
		$p->set_manage_stock( false );
		$p->set_stock_status( 'instock' );
		if ( $term_id ) {
			$p->set_category_ids( array( $term_id ) );
		}
		$img = bb_sideload_asset( $s['poster'], 'Bunny Babe ' . $s['name'] );
		if ( $img ) {
			$p->set_image_id( $img );
		}
		$bottle = bb_sideload_asset( $s['bottle'], 'Bunny Babe ' . $s['name'] . ' bottle' );
		if ( $bottle ) {
			$p->set_gallery_image_ids( array( $bottle ) );
		}
		$p->save();
		/* translators: %s scent */
		$log[] = sprintf( __( 'Created product: %s', 'bunnybabe' ), $p->get_name() );
	}

	// First-order voucher.
	if ( ! wc_get_coupon_id_by_code( $c['first_coupon'] ) ) {
		$coupon = new WC_Coupon();
		$coupon->set_code( $c['first_coupon'] );
		$coupon->set_discount_type( 'fixed_cart' );
		$coupon->set_amount( $c['first_amount'] );
		$coupon->set_usage_limit_per_user( 1 );
		$coupon->set_description( __( 'RM5 off — first order only (checked by phone and email).', 'bunnybabe' ) );
		$coupon->save();
		$log[] = __( 'Created voucher BUNNY5 (RM5, first order).', 'bunnybabe' );
	}

	// Pages.
	$home = get_page_by_path( 'home' );
	if ( ! $home ) {
		$home_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Home',
				'post_name'   => 'home',
				'post_status' => 'publish',
			)
		);
		$log[]   = __( 'Created page "Home".', 'bunnybabe' );
	} else {
		$home_id = $home->ID;
	}
	if ( $home_id && ! is_wp_error( $home_id ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $home_id );
		$log[] = __( '"Home" is now your front page.', 'bunnybabe' );
	}
	if ( ! get_page_by_path( 'track-order' ) ) {
		wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Track Order',
				'post_name'   => 'track-order',
				'post_status' => 'publish',
			)
		);
		$log[] = __( 'Created page "Track Order".', 'bunnybabe' );
	}

	// Classic checkout + cart shortcodes (block checkout does not fit this design).
	foreach ( array(
		'checkout' => '[woocommerce_checkout]',
		'cart'     => '[woocommerce_cart]',
	) as $which => $shortcode ) {
		$pid = wc_get_page_id( $which );
		if ( $pid > 0 ) {
			$post = get_post( $pid );
			if ( $post && false === strpos( $post->post_content, $shortcode ) ) {
				update_post_meta( $pid, '_bb_backup_content', $post->post_content );
				wp_update_post(
					array(
						'ID'           => $pid,
						'post_content' => $shortcode,
					)
				);
				/* translators: %s page */
				$log[] = sprintf( __( 'Switched the %s page to the classic version (backup saved).', 'bunnybabe' ), $which );
			}
		}
	}

	// Shipping zone: Malaysia, flat rate (ship_fee in config).
	$has_zone = false;
	foreach ( WC_Shipping_Zones::get_zones() as $z ) {
		foreach ( $z['zone_locations'] as $loc ) {
			if ( 'country' === $loc->type && 'MY' === $loc->code ) {
				$has_zone = true;
			}
		}
	}
	if ( ! $has_zone ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'Malaysia' );
		$zone->add_location( 'MY', 'country' );
		$zone->save();
		$instance = $zone->add_shipping_method( 'flat_rate' );
		if ( $instance ) {
			update_option(
				'woocommerce_flat_rate_' . $instance . '_settings',
				array(
					'title'      => __( 'Shipping', 'bunnybabe' ),
					'tax_status' => 'none',
					'cost'       => number_format( $c['ship_fee'], 2, '.', '' ),
				)
			);
		}
		/* translators: %s fee */
		$log[] = sprintf( __( 'Created shipping zone "Malaysia" with RM%s flat rate.', 'bunnybabe' ), number_format( $c['ship_fee'], 2 ) );
	}

	flush_rewrite_rules();
	return $log;
}
