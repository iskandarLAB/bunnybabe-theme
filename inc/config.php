<?php
/**
 * Bunny Babe — store rules in one place.
 * Change prices, shipping or scent copy here.
 */
defined( 'ABSPATH' ) || exit;

function bb_config() {
	static $config = null;
	if ( null !== $config ) {
		return $config;
	}
	$config = array(
		// Pricing (RM).
		'unit'          => 39.00, // 1 bottle.
		'duo'           => 59.00, // 2 bottles.
		'pack'          => 99.00, // 5 bottles.

		// Shipping.
		'ship_fee'      => 5.00,  // Flat rate for 1 bottle (set in the Malaysia shipping zone).
		'free_ship_min' => 2,     // FREE shipping from this many bottles.

		// Vouchers.
		'first_coupon'  => 'bunny5', // First order only.
		'first_amount'  => 5,
		'first_min'     => 2,        // Bottles needed for the first-order voucher.
		'next_amount'   => 5,        // Auto-issued for the second order.

		// Product category that counts towards bundles.
		'category'      => 'bunny-babe',

		'scents'        => array(
			'vanilla' => array(
				'name'   => 'Vanilla',
				'sku'    => 'BB-VANILLA',
				'mood'   => 'Warm, cozy & huggable',
				'notes'  => array( 'Vanilla Bean', 'Tonka Bean', 'Caramel', 'Sandalwood' ),
				'desc'   => 'Warm vanilla bean and tonka with a touch of caramel, over sandalwood, amber and soft musk. Grown-up vanilla, not cake vanilla. The huggable one.',
				'poster' => 'vanilla-poster.jpg',
				'bottle' => 'vanilla-bottle.jpg',
			),
			'secret'  => array(
				'name'   => 'Secret',
				'sku'    => 'BB-SECRET',
				'mood'   => 'Bold, sweet & a bit naughty',
				'notes'  => array( 'Honey', 'Gardenia', 'Blood Orange', 'Caramel' ),
				'desc'   => 'Blood orange and mandarin open into honey, gardenia and orange blossom, then melt into beeswax, patchouli and warm caramel. Made for date nights.',
				'poster' => 'secret-poster.jpg',
				'bottle' => 'secret-bottle.jpg',
			),
			'aurelle' => array(
				'name'   => 'Aurelle',
				'sku'    => 'BB-AURELLE',
				'mood'   => 'Soft, clean & feminine',
				'notes'  => array( 'Damask Rose', 'Peony', 'Pink Pepper', 'White Musk' ),
				'desc'   => 'Pink pepper and bergamot lift a soft damask rose and peony heart, resting on clean white musk and soft amber. Your everyday clean-girl scent.',
				'poster' => 'aurelle-poster.jpg',
				'bottle' => 'aurelle-bottle.jpg',
			),
			'marsh'   => array(
				'name'   => 'Marsh',
				'sku'    => 'BB-MARSH',
				'mood'   => 'Super sweet & manja',
				'notes'  => array( 'Marshmallow', 'Whipped Cream', 'Strawberry', 'Vanilla' ),
				'desc'   => 'Sugar and strawberry on top, fluffy marshmallow and whipped cream in the heart, vanilla and white musk underneath. The sweetest in the set.',
				'poster' => 'marsh-poster.jpg',
				'bottle' => 'marsh-bottle.jpg',
			),
			'swift'   => array(
				'name'   => 'Swift',
				'sku'    => 'BB-SWIFT',
				'mood'   => 'Fresh, juicy & playful',
				'notes'  => array( 'Green Apple', 'Pear', 'Jasmine', 'Vanilla Cream' ),
				'desc'   => 'Crisp green apple and pear, a jasmine and peony heart, and a soft base of vanilla cream and musk. Light enough for Malaysian heat.',
				'poster' => 'swift-poster.jpg',
				'bottle' => 'swift-bottle.jpg',
			),
		),
	);
	$config = apply_filters( 'bb_config', $config );
	return $config;
}

/** URL of a file in the theme's assets folder. */
function bb_asset( $path ) {
	return get_template_directory_uri() . '/assets/' . ltrim( $path, '/' );
}

/** Product ID for a scent key, or 0 if setup has not run yet. */
function bb_scent_product_id( $key ) {
	$scents = bb_config()['scents'];
	if ( empty( $scents[ $key ] ) || ! function_exists( 'wc_get_product_id_by_sku' ) ) {
		return 0;
	}
	return (int) wc_get_product_id_by_sku( $scents[ $key ]['sku'] );
}

/** Scent key for a product ID, or ''. */
function bb_scent_key_for_product( $product_id ) {
	foreach ( array_keys( bb_config()['scents'] ) as $key ) {
		if ( bb_scent_product_id( $key ) === (int) $product_id ) {
			return $key;
		}
	}
	return '';
}

/** Bundle price for n bottles: 5-packs first, then pairs, then singles. */
function bb_bundle_price( $n ) {
	$c      = bb_config();
	$n      = max( 0, (int) $n );
	$packs  = intdiv( $n, 5 );
	$rest   = $n % 5;
	$duos   = intdiv( $rest, 2 );
	$single = $rest % 2;
	return round( $packs * $c['pack'] + $duos * $c['duo'] + $single * $c['unit'], 2 );
}

/** Nudge message towards the next price tier. */
function bb_tier_message( $n ) {
	$n = (int) $n;
	if ( $n <= 0 ) {
		return __( 'Add a scent to start', 'bunnybabe' );
	}
	switch ( $n % 5 ) {
		case 0:
			return __( 'Babe Pack price unlocked — RM19.80 a bottle ♡', 'bunnybabe' );
		case 1:
			return 1 === $n
				? __( 'Add 1 more for FREE shipping + Duo price', 'bunnybabe' )
				: __( 'Add 1 more to get the Duo price — RM29.50 a bottle', 'bunnybabe' );
		case 2:
			return __( 'Add 3 more to unlock the Babe Pack — RM19.80 a bottle', 'bunnybabe' );
		case 3:
			return __( 'Add 2 more to unlock the Babe Pack — RM19.80 a bottle', 'bunnybabe' );
		default:
			return __( 'Add 1 more to unlock the Babe Pack — RM19.80 a bottle', 'bunnybabe' );
	}
}

function bb_tier_percent( $n ) {
	$n = (int) $n;
	if ( $n <= 0 ) {
		return 0;
	}
	$rest = $n % 5;
	return 0 === $rest ? 100 : $rest * 20;
}

/** WhatsApp link from the Customizer number. */
function bb_whatsapp_url() {
	$num = preg_replace( '/\D/', '', (string) get_theme_mod( 'bb_whatsapp', '60176842008' ) );
	return $num ? 'https://wa.me/' . $num : '#';
}

/** Track order page URL. */
function bb_track_url() {
	$page = get_page_by_path( 'track-order' );
	return $page ? get_permalink( $page ) : home_url( '/track-order/' );
}

/** Digits only, Malaysian numbers normalised to start with 60. */
function bb_normalize_phone( $phone ) {
	$d = preg_replace( '/\D/', '', (string) $phone );
	if ( '' === $d ) {
		return '';
	}
	if ( 0 === strpos( $d, '0' ) ) {
		$d = '6' . $d;
	}
	return $d;
}
