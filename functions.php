<?php
/**
 * Bunny Babe theme bootstrap.
 */
defined( 'ABSPATH' ) || exit;

define( 'BB_VERSION', '1.5.1' );
define( 'BB_DIR', get_template_directory() );

require BB_DIR . '/inc/config.php';
require BB_DIR . '/inc/customizer.php';
require BB_DIR . '/inc/setup.php';

if ( class_exists( 'WooCommerce' ) ) {
	require BB_DIR . '/inc/cart.php';
	require BB_DIR . '/inc/vouchers.php';
	require BB_DIR . '/inc/tracking.php';
	require BB_DIR . '/inc/social.php';
} else {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-warning"><p><strong>Bunny Babe:</strong> ' . esc_html__( 'Install and activate WooCommerce to turn on the shop, checkout and tracking.', 'bunnybabe' ) . '</p></div>';
		}
	);
}

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'bunnybabe', BB_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'responsive-embeds' );
	}
);

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'bb-fonts', 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600;700&family=Permanent+Marker&display=swap', array(), null );
		wp_enqueue_style( 'bb-style', get_stylesheet_uri(), array( 'bb-fonts' ), BB_VERSION );
		wp_enqueue_script( 'bb-main', bb_asset( 'js/bunnybabe.js' ), array( 'jquery' ), BB_VERSION, true );
		wp_localize_script(
			'bb-main',
			'bbData',
			apply_filters(
				'bb_script_data',
				array(
					'ajax'     => admin_url( 'admin-ajax.php' ),
					'hasWoo'   => class_exists( 'WooCommerce' ),
					'checkout' => '#checkout',
				)
			)
		);
	}
);

/** Favicon from the bunny if no site icon is set. */
add_action(
	'wp_head',
	function () {
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( bb_asset( 'img/logo.png' ) ) . '">' . "\n";
		}
	}
);

/* ------------------------------------------------------------------
 * Ad pixels: Meta, TikTok, Google Analytics 4.
 * IDs live in Appearance → Customize → Bunny Babe → Tracking.
 * Events: PageView, ViewContent, AddToCart, InitiateCheckout, Purchase.
 * ------------------------------------------------------------------ */
function bb_pixel_ids() {
	return array(
		'meta'   => preg_replace( '/\D/', '', (string) get_theme_mod( 'bb_meta_pixel', '' ) ),
		'tiktok' => preg_replace( '/[^A-Z0-9]/', '', strtoupper( (string) get_theme_mod( 'bb_tiktok_pixel', '' ) ) ),
		'ga4'    => preg_replace( '/[^A-Z0-9-]/', '', strtoupper( (string) get_theme_mod( 'bb_ga4', '' ) ) ),
	);
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'bb_tracking',
			array(
				'title'    => __( 'Bunny Babe — Tracking pixels', 'bunnybabe' ),
				'priority' => 31,
			)
		);
		$fields = array(
			'bb_meta_pixel'   => __( 'Meta Pixel / Dataset ID (numbers)', 'bunnybabe' ),
			'bb_tiktok_pixel' => __( 'TikTok Pixel ID (e.g. C1ABCD...)', 'bunnybabe' ),
			'bb_ga4'          => __( 'Google Analytics 4 ID (G-XXXXXXX)', 'bunnybabe' ),
		);
		foreach ( $fields as $id => $label ) {
			$wp_customize->add_setting( $id, array( 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'section' => 'bb_tracking', 'type' => 'text' ) );
		}
	}
);

/** Base codes + a tiny bbTrack() helper the theme JS calls. */
add_action(
	'wp_head',
	function () {
		if ( is_admin() ) {
			return;
		}
		$ids = bb_pixel_ids();
		if ( ! array_filter( $ids ) ) {
			echo "<script>window.bbTrack=function(){};</script>\n";
			return;
		}
		?>
<script>
<?php if ( $ids['meta'] ) : ?>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init','<?php echo esc_js( $ids['meta'] ); ?>'<?php $bb_am = bb_thankyou_am(); echo $bb_am ? ',' . wp_json_encode( $bb_am ) : ''; ?>);fbq('track','PageView');
<?php endif; ?>
<?php if ( $ids['tiktok'] ) : ?>
!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript",o.async=!0,o.src=r+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('<?php echo esc_js( $ids['tiktok'] ); ?>');ttq.page();}(window,document,'ttq');
<?php endif; ?>
<?php if ( $ids['ga4'] ) : ?>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $ids['ga4'] ); ?>');
<?php endif; ?>
window.bbTrack=function(ev,d){d=d||{};var v=d.value||0,ids=d.ids||[],q=d.qty||1,o={value:v,currency:'MYR',content_ids:ids,content_type:'product',num_items:q};
try{if(window.fbq){fbq('track',ev,o,d.eid?{eventID:d.eid}:undefined);}}catch(e){}
try{if(window.ttq){var tm={ViewContent:'ViewContent',AddToCart:'AddToCart',InitiateCheckout:'InitiateCheckout',Purchase:'CompletePayment'};ttq.track(tm[ev]||ev,{value:v,currency:'MYR',contents:ids.map(function(i){return{content_id:String(i),quantity:q};}),content_type:'product'},d.eid?{event_id:d.eid}:undefined);}}catch(e){}
try{if(window.gtag){var gm={ViewContent:'view_item',AddToCart:'add_to_cart',InitiateCheckout:'begin_checkout',Purchase:'purchase'};var g={currency:'MYR',value:v,items:ids.map(function(i){return{item_id:String(i),quantity:q};})};if(d.eid){g.transaction_id=d.eid;}gtag('event',gm[ev]||ev,g);}}catch(e){}};
</script>
<?php if ( $ids['ga4'] ) : ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ids['ga4'] ); ?>"></script>
<?php endif; ?>
		<?php
	},
	1
);

/** Advanced matching data for Meta on the thank-you page (used in the single fbq init). */
function bb_thankyou_am() {
	if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
		return array();
	}
	global $wp;
	$order_id = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
	$order    = $order_id ? wc_get_order( $order_id ) : null;
	$key      = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! $order || ! hash_equals( $order->get_order_key(), $key ) ) {
		return array();
	}
	return array_filter(
		array(
			'em'          => strtolower( trim( (string) $order->get_billing_email() ) ),
			'ph'          => bb_normalize_phone( $order->get_billing_phone() ),
			'fn'          => strtolower( trim( (string) strtok( (string) $order->get_billing_first_name(), ' ' ) ) ),
			'ct'          => strtolower( preg_replace( '/\s+/', '', (string) $order->get_billing_city() ) ),
			'zp'          => (string) $order->get_billing_postcode(),
			'country'     => 'my',
			'external_id' => (string) bb_normalize_phone( $order->get_billing_phone() ),
		)
	);
}

/** Purchase event on the thank-you page (once per order), with advanced matching. */
add_action(
	'woocommerce_thankyou',
	function ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || $order->get_meta( '_bb_px_sent' ) || ! array_filter( bb_pixel_ids() ) ) {
			return;
		}
		// Only count real sales: paid, or waiting for a manual check (bank transfer).
		if ( ! $order->is_paid() && 'on-hold' !== $order->get_status() ) {
			return;
		}
		$order->update_meta_data( '_bb_px_sent', 1 );
		$order->save();
		$ids = array();
		$qty = 0;
		foreach ( $order->get_items() as $item ) {
			$ids[] = (string) $item->get_product_id();
			$qty  += (int) $item->get_quantity();
		}
		$meta = bb_pixel_ids()['meta'];
		$am   = array_filter(
			array(
				'em'      => strtolower( trim( (string) $order->get_billing_email() ) ),
				'ph'      => bb_normalize_phone( $order->get_billing_phone() ),
				'fn'      => strtolower( trim( strtok( (string) $order->get_billing_first_name(), ' ' ) ) ),
				'ct'      => strtolower( preg_replace( '/\s+/', '', (string) $order->get_billing_city() ) ),
				'zp'      => (string) $order->get_billing_postcode(),
				'country' => 'my',
			)
		);
		$data = array(
			'value' => (float) $order->get_total(),
			'ids'   => $ids,
			'qty'   => $qty,
			'eid'   => 'order_' . $order->get_id(),
		);
		echo '<script>';
		echo 'if(window.ttq&&ttq.identify){ttq.identify(' . wp_json_encode( array_filter( array( 'email' => isset( $am['em'] ) ? $am['em'] : '', 'phone_number' => isset( $am['ph'] ) ? '+' . $am['ph'] : '' ) ) ) . ');}';
		echo 'window.bbTrack&&window.bbTrack("Purchase",' . wp_json_encode( $data ) . ');</script>';
	},
	1
);

/* ------------------------------------------------------------------
 * Meta Conversions API: server-side Purchase.
 * Same event_id as the browser pixel (order_{id}) so Meta deduplicates.
 * Token lives in Appearance > Customize > Bunny Babe > Tracking pixels.
 * ------------------------------------------------------------------ */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_setting( 'bb_meta_capi_token', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'bb_meta_capi_token', array( 'label' => __( 'Meta Conversions API access token', 'bunnybabe' ), 'section' => 'bb_tracking', 'type' => 'text' ) );
	},
	20
);

function bb_capi_purchase( $order_id ) {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return;
	}
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_bb_capi_sent' ) ) {
		return;
	}
	$ids_all = bb_pixel_ids();
	$pixel   = $ids_all['meta'];
	$token   = trim( (string) get_theme_mod( 'bb_meta_capi_token', '' ) );
	if ( ! $pixel || ! $token ) {
		return;
	}
	$h = function ( $v ) {
		$v = strtolower( trim( (string) $v ) );
		return '' === $v ? null : hash( 'sha256', $v );
	};
	$phone = preg_replace( '/\D/', '', (string) $order->get_billing_phone() );
	if ( $phone && 0 === strpos( $phone, '0' ) ) {
		$phone = '6' . $phone;
	}
	$ud = array_filter(
		array(
			'em'                => $h( $order->get_billing_email() ),
			'ph'                => $h( $phone ),
			'fn'                => $h( $order->get_billing_first_name() ),
			'ln'                => $h( $order->get_billing_last_name() ),
			'ct'                => $h( preg_replace( '/\s+/', '', (string) $order->get_billing_city() ) ),
			'zp'                => $h( $order->get_billing_postcode() ),
			'country'           => $h( 'my' ),
			'external_id'       => $h( $order->get_billing_email() ? $order->get_billing_email() : $phone ),
			'client_ip_address' => $order->get_customer_ip_address() ? $order->get_customer_ip_address() : null,
			'client_user_agent' => $order->get_customer_user_agent() ? $order->get_customer_user_agent() : null,
			'fbp'               => $order->get_meta( '_bb_fbp' ) ? $order->get_meta( '_bb_fbp' ) : null,
			'fbc'               => $order->get_meta( '_bb_fbc' ) ? $order->get_meta( '_bb_fbc' ) : null,
		)
	);
	$content_ids = array();
	$qty         = 0;
	foreach ( $order->get_items() as $item ) {
		$content_ids[] = (string) $item->get_product_id();
		$qty          += (int) $item->get_quantity();
	}
	$created = $order->get_date_created();
	$body    = array(
		'data' => array(
			array(
				'event_name'       => 'Purchase',
				'event_time'       => $created ? $created->getTimestamp() : time(),
				'event_id'         => 'order_' . $order->get_id(),
				'action_source'    => 'website',
				'event_source_url' => $order->get_checkout_order_received_url(),
				'user_data'        => $ud,
				'custom_data'      => array(
					'currency'     => 'MYR',
					'value'        => (float) $order->get_total(),
					'content_ids'  => $content_ids,
					'content_type' => 'product',
					'num_items'    => $qty,
					'order_id'     => (string) $order->get_order_number(),
				),
			),
		),
	);
	$res = wp_remote_post(
		'https://graph.facebook.com/v21.0/' . $pixel . '/events?access_token=' . rawurlencode( $token ),
		array(
			'timeout' => 8,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		)
	);
	if ( ! is_wp_error( $res ) && 200 === (int) wp_remote_retrieve_response_code( $res ) ) {
		$order->update_meta_data( '_bb_capi_sent', time() );
		$order->save();
	} else {
		$order->add_order_note( 'Meta CAPI failed: ' . ( is_wp_error( $res ) ? $res->get_error_message() : substr( (string) wp_remote_retrieve_body( $res ), 0, 300 ) ) );
	}
}
add_action( 'woocommerce_payment_complete', 'bb_capi_purchase' );
add_action( 'woocommerce_order_status_processing', 'bb_capi_purchase' );
add_action( 'woocommerce_order_status_completed', 'bb_capi_purchase' );

/* Keep the Meta browser cookies on the order for better matching. */
function bb_capi_save_cookies( $order ) {
	if ( ! empty( $_COOKIE['_fbp'] ) ) {
		$order->update_meta_data( '_bb_fbp', sanitize_text_field( wp_unslash( $_COOKIE['_fbp'] ) ) );
	}
	if ( ! empty( $_COOKIE['_fbc'] ) ) {
		$order->update_meta_data( '_bb_fbc', sanitize_text_field( wp_unslash( $_COOKIE['_fbc'] ) ) );
	}
}
add_action( 'woocommerce_checkout_create_order', 'bb_capi_save_cookies' );
add_action( 'woocommerce_store_api_checkout_update_order_meta', 'bb_capi_save_cookies' );

/* Meta domain verification for bunnybabe.my (Reez BM). */
add_action(
	'wp_head',
	function () {
		echo '<meta name="facebook-domain-verification" content="g9xfl118dzcelq81zjq6gihh3tcx0u" />' . "\n";
	},
	0
);
