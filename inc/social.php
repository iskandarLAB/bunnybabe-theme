<?php
/**
 * Social proof: "recent purchase" pop-up (from real paid orders) and testimonials.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------------------------
 * Recent purchases.
 * ------------------------------------------------------------------ */

/** Up to 12 paid orders from the last 14 days: first name, city, what they bought, when. */
function bb_recent_purchases() {
	if ( ! function_exists( 'wc_get_orders' ) || ! get_theme_mod( 'bb_show_recent', true ) ) {
		return array();
	}
	$cached = get_transient( 'bb_recent' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$orders = wc_get_orders(
		array(
			'limit'        => 12,
			'status'       => array( 'processing', 'completed', 'shipped', 'out-for-delivery' ),
			'date_created' => '>' . ( time() - 14 * DAY_IN_SECONDS ),
			'orderby'      => 'date',
			'order'        => 'DESC',
		)
	);
	$out = array();
	foreach ( $orders as $order ) {
		$scents = array();
		foreach ( $order->get_items() as $item ) {
			if ( get_post_meta( $item->get_product_id(), '_bb_test_product', true ) ) {
				continue;
			}
			$key = bb_scent_key_for_product( $item->get_product_id() );
			if ( $key ) {
				$scents[ $key ] = ( isset( $scents[ $key ] ) ? $scents[ $key ] : 0 ) + (int) $item->get_quantity();
			}
		}
		if ( ! $scents ) {
			continue;
		}
		arsort( $scents );
		$first = (string) key( $scents );
		$name  = ucfirst( strtolower( (string) strtok( trim( (string) $order->get_billing_first_name() ), ' ' ) ) );
		$when  = $order->get_date_paid() ? $order->get_date_paid() : $order->get_date_created();
		$out[] = array(
			'name'  => $name ? mb_substr( $name, 0, 14 ) : __( 'Someone', 'bunnybabe' ),
			'city'  => ucwords( strtolower( (string) $order->get_billing_city() ) ),
			'scent' => $first,
			'label' => bb_config()['scents'][ $first ]['name'],
			'more'  => array_sum( $scents ) - 1,
			'img'   => bb_asset( 'img/' . bb_config()['scents'][ $first ]['bottle'] ),
			'ts'    => $when ? $when->getTimestamp() : time(),
		);
	}
	set_transient( 'bb_recent', $out, 10 * MINUTE_IN_SECONDS );
	return $out;
}

// Show a new order in the pop-up straight away.
foreach ( array( 'woocommerce_order_status_processing', 'woocommerce_order_status_completed', 'woocommerce_order_status_cancelled', 'woocommerce_order_status_refunded' ) as $bb_hook ) {
	add_action(
		$bb_hook,
		function () {
			delete_transient( 'bb_recent' );
		}
	);
}

add_filter(
	'bb_script_data',
	function ( $data ) {
		if ( is_front_page() ) {
			$data['recent'] = bb_recent_purchases();
		}
		return $data;
	}
);

/* ------------------------------------------------------------------
 * Testimonials: WP Admin → Testimonials (name, quote, scent, stars, photo),
 * plus any image dropped into assets/img/testimonials/ (review screenshots).
 * ------------------------------------------------------------------ */
add_action(
	'init',
	function () {
		register_post_type(
			'bb_testimonial',
			array(
				'labels'        => array(
					'name'          => __( 'Testimonials', 'bunnybabe' ),
					'singular_name' => __( 'Testimonial', 'bunnybabe' ),
					'add_new_item'  => __( 'Add testimonial', 'bunnybabe' ),
					'edit_item'     => __( 'Edit testimonial', 'bunnybabe' ),
					'featured_image' => __( 'Customer photo', 'bunnybabe' ),
					'set_featured_image' => __( 'Set customer photo', 'bunnybabe' ),
				),
				'public'        => false,
				'show_ui'       => true,
				'menu_icon'     => 'dashicons-heart',
				'menu_position' => 56,
				'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
			)
		);
	}
);

add_filter(
	'enter_title_here',
	function ( $text, $post ) {
		return 'bb_testimonial' === $post->post_type ? __( 'Customer name (e.g. Aisyah, Shah Alam)', 'bunnybabe' ) : $text;
	},
	10,
	2
);

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'bb_testimonial_meta', __( 'Scent & stars', 'bunnybabe' ), 'bb_testimonial_box', 'bb_testimonial', 'side' );
	}
);

function bb_testimonial_box( $post ) {
	wp_nonce_field( 'bb_testimonial_save', 'bb_testimonial_nonce' );
	$scent = get_post_meta( $post->ID, '_bb_scent', true );
	$stars = (int) get_post_meta( $post->ID, '_bb_stars', true );
	$stars = $stars ? $stars : 5;
	?>
	<p><label for="bb_scent"><strong><?php esc_html_e( 'Scent', 'bunnybabe' ); ?></strong></label><br>
	<select id="bb_scent" name="bb_scent" style="width:100%">
		<option value=""><?php esc_html_e( '— none —', 'bunnybabe' ); ?></option>
		<?php foreach ( bb_config()['scents'] as $key => $s ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $scent, $key ); ?>><?php echo esc_html( $s['name'] ); ?></option>
		<?php endforeach; ?>
	</select></p>
	<p><label for="bb_stars"><strong><?php esc_html_e( 'Stars', 'bunnybabe' ); ?></strong></label><br>
	<select id="bb_stars" name="bb_stars" style="width:100%">
		<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
			<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $stars, $i ); ?>><?php echo esc_html( str_repeat( '★', $i ) ); ?></option>
		<?php endfor; ?>
	</select></p>
	<p class="description"><?php esc_html_e( 'Write the review in the main box. Add the photo with "Set customer photo". Use "Order" to change the position.', 'bunnybabe' ); ?></p>
	<?php
}

add_action(
	'save_post_bb_testimonial',
	function ( $post_id ) {
		if ( ! isset( $_POST['bb_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bb_testimonial_nonce'] ) ), 'bb_testimonial_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$scent = isset( $_POST['bb_scent'] ) ? sanitize_key( wp_unslash( $_POST['bb_scent'] ) ) : '';
		update_post_meta( $post_id, '_bb_scent', isset( bb_config()['scents'][ $scent ] ) ? $scent : '' );
		update_post_meta( $post_id, '_bb_stars', isset( $_POST['bb_stars'] ) ? max( 1, min( 5, absint( $_POST['bb_stars'] ) ) ) : 5 );
	}
);

/** Cards for the carousel: admin testimonials first, then screenshots from the folder. */
function bb_testimonials() {
	$cards = array();
	$posts = get_posts(
		array(
			'post_type'      => 'bb_testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
		)
	);
	foreach ( $posts as $p ) {
		$scent   = (string) get_post_meta( $p->ID, '_bb_scent', true );
		$stars   = (int) get_post_meta( $p->ID, '_bb_stars', true );
		$cards[] = array(
			'img'   => get_the_post_thumbnail_url( $p, 'medium_large' ),
			'name'  => get_the_title( $p ),
			'quote' => wp_strip_all_tags( $p->post_content ),
			'scent' => isset( bb_config()['scents'][ $scent ] ) ? bb_config()['scents'][ $scent ]['name'] : '',
			'stars' => $stars ? $stars : 5,
		);
	}
	$files = glob( BB_DIR . '/assets/img/testimonials/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE );
	if ( $files ) {
		natcasesort( $files );
		foreach ( $files as $f ) {
			$cards[] = array(
				'img'   => bb_asset( 'img/testimonials/' . rawurlencode( basename( $f ) ) ),
				'name'  => '',
				'quote' => '',
				'scent' => '',
				'stars' => 0,
			);
		}
	}
	return $cards;
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_setting(
			'bb_show_recent',
			array(
				'default'           => true,
				'sanitize_callback' => 'wp_validate_boolean',
			)
		);
		$wp_customize->add_control(
			'bb_show_recent',
			array(
				'label'       => __( 'Show "recent purchase" pop-up', 'bunnybabe' ),
				'description' => __( 'Uses real paid orders from the last 14 days (first name + city only).', 'bunnybabe' ),
				'section'     => 'bb_settings',
				'type'        => 'checkbox',
			)
		);
	},
	20
);
