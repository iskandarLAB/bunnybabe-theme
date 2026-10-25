<?php
/**
 * Order tracking: extra statuses, courier box on the order screen, lookup.
 */
defined( 'ABSPATH' ) || exit;

/* Extra statuses: Shipped, Out for delivery. */
add_action(
	'init',
	function () {
		register_post_status(
			'wc-shipped',
			array(
				'label'                     => _x( 'Shipped', 'Order status', 'bunnybabe' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s count */
				'label_count'               => _n_noop( 'Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'bunnybabe' ),
			)
		);
		register_post_status(
			'wc-out-for-delivery',
			array(
				'label'                     => _x( 'Out for delivery', 'Order status', 'bunnybabe' ),
				'public'                    => false,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s count */
				'label_count'               => _n_noop( 'Out for delivery <span class="count">(%s)</span>', 'Out for delivery <span class="count">(%s)</span>', 'bunnybabe' ),
			)
		);
	}
);

add_filter(
	'wc_order_statuses',
	function ( $statuses ) {
		$out = array();
		foreach ( $statuses as $key => $label ) {
			$out[ $key ] = $label;
			if ( 'wc-processing' === $key ) {
				$out['wc-shipped']          = _x( 'Shipped', 'Order status', 'bunnybabe' );
				$out['wc-out-for-delivery'] = _x( 'Out for delivery', 'Order status', 'bunnybabe' );
			}
		}
		return $out;
	}
);

// Count these as paid for reports and "is paid" checks.
add_filter(
	'woocommerce_order_is_paid_statuses',
	function ( $s ) {
		return array_unique( array_merge( $s, array( 'shipped', 'out-for-delivery' ) ) );
	}
);

// Bulk actions on the orders list.
foreach ( array( 'bulk_actions-edit-shop_order', 'bulk_actions-woocommerce_page_wc-orders' ) as $bb_hook ) {
	add_filter(
		$bb_hook,
		function ( $actions ) {
			$actions['mark_shipped']          = __( 'Change status to shipped', 'bunnybabe' );
			$actions['mark_out-for-delivery'] = __( 'Change status to out for delivery', 'bunnybabe' );
			return $actions;
		}
	);
}

/* Courier + tracking number box on the order edit screen. */
add_action(
	'add_meta_boxes',
	function () {
		foreach ( array( 'shop_order', 'woocommerce_page_wc-orders' ) as $screen ) {
			add_meta_box( 'bb_tracking', __( 'Bunny Babe tracking', 'bunnybabe' ), 'bb_tracking_box', $screen, 'side', 'high' );
		}
	}
);

function bb_tracking_box( $post_or_order ) {
	$order = ( $post_or_order instanceof WP_Post ) ? wc_get_order( $post_or_order->ID ) : $post_or_order;
	if ( ! $order ) {
		return;
	}
	wp_nonce_field( 'bb_tracking_save', 'bb_tracking_nonce' );
	$courier  = $order->get_meta( '_bb_courier' );
	$tracking = $order->get_meta( '_bb_tracking_no' );
	?>
	<p>
		<label for="bb_courier"><strong><?php esc_html_e( 'Courier', 'bunnybabe' ); ?></strong></label><br>
		<input type="text" id="bb_courier" name="bb_courier" value="<?php echo esc_attr( $courier ); ?>" placeholder="J&amp;T, Ninja Van, Pos Laju…" style="width:100%">
	</p>
	<p>
		<label for="bb_tracking_no"><strong><?php esc_html_e( 'Tracking number (AWB)', 'bunnybabe' ); ?></strong></label><br>
		<input type="text" id="bb_tracking_no" name="bb_tracking_no" value="<?php echo esc_attr( $tracking ); ?>" style="width:100%">
	</p>
	<p class="description"><?php esc_html_e( 'Shown to the customer on the Track order page. Paste it from EasyParcel after booking.', 'bunnybabe' ); ?></p>
	<?php
}

add_action(
	'woocommerce_process_shop_order_meta',
	function ( $order_id ) {
		if ( ! isset( $_POST['bb_tracking_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bb_tracking_nonce'] ) ), 'bb_tracking_save' ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$order->update_meta_data( '_bb_courier', isset( $_POST['bb_courier'] ) ? sanitize_text_field( wp_unslash( $_POST['bb_courier'] ) ) : '' );
		$order->update_meta_data( '_bb_tracking_no', isset( $_POST['bb_tracking_no'] ) ? sanitize_text_field( wp_unslash( $_POST['bb_tracking_no'] ) ) : '' );
		$order->save();
	},
	50
);

/* Lookup for the Track order page. */
function bb_track_lookup( $number, $phone ) {
	$id    = absint( preg_replace( '/\D/', '', (string) $number ) );
	$phone = bb_normalize_phone( $phone );
	if ( ! $id || strlen( $phone ) < 10 ) {
		return new WP_Error( 'bb_input', __( 'Fill in both your order number and phone number.', 'bunnybabe' ) );
	}
	$order = wc_get_order( $id );
	if ( ! $order || ! ( $order instanceof WC_Order ) ) {
		return new WP_Error( 'bb_none', __( "We couldn't find that order. Check the number and phone, or WhatsApp us.", 'bunnybabe' ) );
	}
	$order_phone = bb_normalize_phone( $order->get_billing_phone() );
	if ( substr( $order_phone, -9 ) !== substr( $phone, -9 ) ) {
		return new WP_Error( 'bb_none', __( "We couldn't find that order. Check the number and phone, or WhatsApp us.", 'bunnybabe' ) );
	}
	return $order;
}

/** Which step (0–4) an order is at, or -1 if cancelled/failed. */
function bb_track_step( $order ) {
	switch ( $order->get_status() ) {
		case 'pending':
		case 'on-hold':
			return 0;
		case 'processing':
			return 1;
		case 'shipped':
			return 2;
		case 'out-for-delivery':
			return 3;
		case 'completed':
			return 4;
		default:
			return -1;
	}
}

/** Date a status was reached, from order notes (best effort). */
function bb_track_dates( $order ) {
	$fmt   = 'j M, g:i A';
	$dates = array( $order->get_date_created() ? $order->get_date_created()->date_i18n( $fmt ) : '' );
	$dates[1] = $order->get_date_paid() ? $order->get_date_paid()->date_i18n( $fmt ) : '';
	$dates[4] = $order->get_date_completed() ? $order->get_date_completed()->date_i18n( $fmt ) : '';
	return $dates;
}
