<?php
/**
 * Vouchers + leads.
 *  - BUNNY5: RM5 off, first order only (checked by phone and email).
 *  - After a first paid order, a unique RM5 code is issued for the second order.
 *  - "Claim voucher" numbers are saved under WooCommerce → Voucher leads.
 */
defined( 'ABSPATH' ) || exit;

/** Paid-ish order statuses (without "wc-"). */
function bb_paid_statuses() {
	return array( 'processing', 'completed', 'on-hold', 'shipped', 'out-for-delivery' );
}

/** Save a normalised phone on every order so we can find repeat customers. */
add_action(
	'woocommerce_checkout_create_order',
	function ( $order ) {
		$phone = bb_normalize_phone( $order->get_billing_phone() );
		if ( $phone ) {
			$order->update_meta_data( '_bb_phone', $phone );
		}
	}
);

/** Does this customer already have a paid order? */
function bb_customer_has_orders( $email, $phone, $exclude_id = 0 ) {
	$base = array(
		'limit'   => 1,
		'return'  => 'ids',
		'status'  => bb_paid_statuses(),
		'exclude' => $exclude_id ? array( (int) $exclude_id ) : array(),
	);
	if ( $email ) {
		$found = wc_get_orders( array_merge( $base, array( 'billing_email' => $email ) ) );
		if ( ! empty( $found ) ) {
			return true;
		}
	}
	$phone = bb_normalize_phone( $phone );
	if ( $phone ) {
		$found = wc_get_orders(
			array_merge(
				$base,
				array(
					'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'   => '_bb_phone',
							'value' => $phone,
						),
					),
				)
			)
		);
		if ( ! empty( $found ) ) {
			return true;
		}
	}
	return false;
}

/** BUNNY5 is for first orders only. */
add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		$code = bb_config()['first_coupon'];
		if ( ! WC()->cart || ! WC()->cart->has_discount( $code ) ) {
			return;
		}
		$email = isset( $data['billing_email'] ) ? $data['billing_email'] : '';
		$phone = isset( $data['billing_phone'] ) ? $data['billing_phone'] : '';
		if ( bb_customer_has_orders( $email, $phone ) ) {
			WC()->cart->remove_coupon( $code );
			$errors->add(
				'bb_first_order',
				sprintf(
					/* translators: %s coupon code */
					__( '%s is for first orders only, so we removed it. Use the voucher we sent you after your first order instead.', 'bunnybabe' ),
					strtoupper( $code )
				)
			);
		}
	},
	10,
	2
);

/* ------------------------------------------------------------------
 * Second-order voucher.
 * ------------------------------------------------------------------ */
function bb_issue_next_voucher( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order || $order->get_meta( '_bb_next_voucher' ) ) {
		return;
	}
	// Only for a customer's first paid order.
	if ( bb_customer_has_orders( $order->get_billing_email(), $order->get_billing_phone(), $order->get_id() ) ) {
		$order->update_meta_data( '_bb_next_voucher', '-' );
		$order->save();
		return;
	}
	$code = '';
	for ( $i = 0; $i < 5; $i++ ) {
		$try = 'BB' . strtoupper( wp_generate_password( 6, false, false ) );
		if ( ! wc_get_coupon_id_by_code( $try ) ) {
			$code = $try;
			break;
		}
	}
	if ( ! $code ) {
		return;
	}
	$coupon = new WC_Coupon();
	$coupon->set_code( $code );
	$coupon->set_discount_type( 'fixed_cart' );
	$coupon->set_amount( bb_config()['next_amount'] );
	$coupon->set_usage_limit( 1 );
	$coupon->set_usage_limit_per_user( 1 );
	if ( $order->get_billing_email() ) {
		$coupon->set_email_restrictions( array( $order->get_billing_email() ) );
	}
	/* translators: %s order number */
	$coupon->set_description( sprintf( __( 'Second-order RM5 voucher from order #%s', 'bunnybabe' ), $order->get_order_number() ) );
	$coupon->save();

	$order->update_meta_data( '_bb_next_voucher', $code );
	/* translators: %s voucher code */
	$order->add_order_note( sprintf( __( 'Second-order voucher issued: %s (send to customer on WhatsApp).', 'bunnybabe' ), $code ) );
	$order->save();
}
add_action( 'woocommerce_order_status_processing', 'bb_issue_next_voucher' );
add_action( 'woocommerce_order_status_completed', 'bb_issue_next_voucher' );

/** The real code, or ''. */
function bb_next_voucher_code( $order ) {
	$code = $order ? (string) $order->get_meta( '_bb_next_voucher' ) : '';
	return ( '' === $code || '-' === $code ) ? '' : $code;
}

/** Thank-you page: TikTok-style order status with shipping progress. */
add_action(
	'woocommerce_thankyou',
	function ( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$code    = bb_next_voucher_code( $order );
		$failed  = in_array( $order->get_status(), array( 'failed', 'cancelled' ), true );
		$unpaid  = 'pending' === $order->get_status() && $order->needs_payment();
		$step    = function_exists( 'bb_track_step' ) ? max( 0, bb_track_step( $order ) ) : 0;
		if ( 'on-hold' === $order->get_status() ) {
			$step = 1; // Waiting for a manual payment check (e.g. bank transfer).
		}
		if ( $failed ) {
			$head = __( 'Payment not completed', 'bunnybabe' );
		} elseif ( $unpaid ) {
			$head = __( 'Waiting for your payment', 'bunnybabe' );
		} elseif ( $order->is_paid() && 'cod' !== $order->get_payment_method() ) {
			$head = __( 'Payment successful', 'bunnybabe' );
		} else {
			$head = __( 'Order confirmed', 'bunnybabe' );
		}
		$dates  = function_exists( 'bb_track_dates' ) ? bb_track_dates( $order ) : array();
		$titles = array( __( 'Order placed', 'bunnybabe' ), __( 'Packed with love', 'bunnybabe' ), __( 'Shipped', 'bunnybabe' ), __( 'Out for delivery', 'bunnybabe' ), __( 'Delivered', 'bunnybabe' ) );
		$notes  = array( '', __( "We're packing your order now", 'bunnybabe' ), __( "We'll WhatsApp you the tracking number", 'bunnybabe' ), '', '' );
		$icons  = array(
			'<path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
			'<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>',
			'<rect x="2" y="7" width="12" height="9" rx="1"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="6.5" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
			'<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
			'<path d="M3 11l9-7 9 7v9H3z"/><path d="M9 20v-6h6v6"/>',
		);
		if ( empty( $dates[1] ) && $order->get_date_paid() ) {
			$dates[1] = $order->get_date_paid()->date_i18n( 'j M, g:i A' );
		}
		?>
		<div class="bb-ty">
			<div class="bb-ty__head<?php echo ( $failed || $unpaid ) ? ' is-failed' : ''; ?>">
				<span class="bb-ty__check" aria-hidden="true"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><?php echo $failed ? '<path d="M6 6l12 12M18 6L6 18"/>' : ( $unpaid ? '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>' : '<path d="M5 12.5l4.5 4.5L19 7.5"/>' ); ?></svg></span>
				<strong><?php echo esc_html( $head ); ?></strong>
				<span><?php echo esc_html( sprintf( /* translators: 1 order no, 2 total */ __( 'Order #%1$s · %2$s', 'bunnybabe' ), $order->get_order_number(), wp_strip_all_tags( wc_price( $order->get_total() ) ) ) ); ?></span>
				<?php if ( $failed || $unpaid ) : ?>
					<?php if ( $unpaid ) : ?>
						<span><?php esc_html_e( "We haven't received your payment yet. If you just paid, refresh this page in a moment.", 'bunnybabe' ); ?></span>
					<?php endif; ?>
					<?php if ( $order->needs_payment() ) : ?>
						<a class="bb-btn bb-btn--black" style="margin-top:12px" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Complete payment', 'bunnybabe' ); ?></a>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<?php if ( ! $failed && ! $unpaid ) : ?>
			<div class="bb-ty__card">
				<div class="bb-ty__cardhead"><strong><?php esc_html_e( 'Shipping status', 'bunnybabe' ); ?></strong><span><?php esc_html_e( 'EasyParcel', 'bunnybabe' ); ?></span></div>
				<ol class="bb-steps bb-steps--ty">
					<?php foreach ( $titles as $i => $title ) : ?>
						<?php
						$cls    = $i < $step ? 'is-done' : ( $i === $step ? 'is-current' : '' );
						$detail = ( $i <= $step && ! empty( $dates[ $i ] ) ) ? $dates[ $i ] : ( $i === $step && $notes[ $i ] ? $notes[ $i ] : ( $i > $step ? __( 'Coming soon', 'bunnybabe' ) : __( 'Done', 'bunnybabe' ) ) );
						if ( $i === $step && $notes[ $i ] ) {
							$detail = $notes[ $i ];
						}
						?>
						<li class="bb-step <?php echo esc_attr( $cls ); ?>">
							<div class="bb-step__rail">
								<span class="bb-step__dot" style="animation-delay:<?php echo esc_attr( $i * 0.2 ); ?>s"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $icons[ $i ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></svg></span>
								<?php if ( $i < 4 ) : ?>
									<span class="bb-step__line"><?php if ( $i < $step ) : ?><span style="animation-delay:<?php echo esc_attr( $i * 0.2 + 0.2 ); ?>s"></span><?php endif; ?></span>
								<?php endif; ?>
							</div>
							<div class="bb-step__text"><strong><?php echo esc_html( $title ); ?></strong><span><?php echo esc_html( $detail ); ?></span></div>
						</li>
					<?php endforeach; ?>
				</ol>
			</div>
			<?php endif; ?>

			<div class="bb-ty__card">
				<div class="bb-ty__cardhead"><strong><?php esc_html_e( 'Your order', 'bunnybabe' ); ?></strong><span>#<?php echo esc_html( $order->get_order_number() ); ?></span></div>
				<?php
				foreach ( $order->get_items() as $item ) :
					$scent = bb_scent_key_for_product( $item->get_product_id() );
					$img   = $scent ? bb_asset( 'img/' . bb_config()['scents'][ $scent ]['bottle'] ) : '';
					?>
					<div class="bb-bag__item">
						<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" width="60" height="60"><?php endif; ?>
						<div class="bb-bag__meta"><strong><?php echo esc_html( $item->get_name() ); ?></strong><span>× <?php echo esc_html( $item->get_quantity() ); ?></span></div>
					</div>
				<?php endforeach; ?>
				<div class="bb-lines">
					<?php foreach ( $order->get_order_item_totals() as $key => $row ) : ?>
						<div class="<?php echo 'order_total' === $key ? 'is-total' : ''; ?>"><strong style="font-weight:<?php echo 'order_total' === $key ? 700 : 400; ?>"><?php echo esc_html( wp_strip_all_tags( $row['label'] ) ); ?></strong><strong><?php echo wp_kses_post( $row['value'] ); ?></strong></div>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $code ) : ?>
				<div class="bb-ty__card bb-ty__voucher">
					<strong><?php esc_html_e( 'RM5 off your next order ♡', 'bunnybabe' ); ?></strong>
					<span class="bb-code"><?php echo esc_html( $code ); ?></span>
					<small><?php esc_html_e( "It's applied automatically next time you check out with this WhatsApp number.", 'bunnybabe' ); ?></small>
				</div>
			<?php endif; ?>

			<div class="bb-ty__btns">
				<form method="post" action="<?php echo esc_url( bb_track_url() ); ?>">
					<input type="hidden" name="order" value="<?php echo esc_attr( $order->get_order_number() ); ?>">
					<input type="hidden" name="phone" value="<?php echo esc_attr( $order->get_billing_phone() ); ?>">
					<button class="bb-btn bb-btn--black" type="submit" name="bb_track" value="1"><?php esc_html_e( 'Track order', 'bunnybabe' ); ?></button>
				</form>
				<a class="bb-btn bb-btn--white" href="<?php echo esc_url( bb_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp us', 'bunnybabe' ); ?></a>
				<a class="bb-btn bb-btn--pink" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Shop again', 'bunnybabe' ); ?></a>
			</div>
		</div>
		<?php
	},
	5
);

// Our card above already lists items + totals; WooCommerce's own details table
// (with the full address) was only hidden by CSS, so don't print it at all.
add_action(
	'init',
	function () {
		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
	}
);

/** Order emails to the customer show the next-order code. */
add_action(
	'woocommerce_email_after_order_table',
	function ( $order, $sent_to_admin, $plain_text ) {
		if ( $sent_to_admin ) {
			return;
		}
		$code = bb_next_voucher_code( $order );
		if ( ! $code ) {
			return;
		}
		if ( $plain_text ) {
			/* translators: %s code */
			echo "\n" . esc_html( sprintf( __( 'RM5 off your next Bunny Babe order: %s', 'bunnybabe' ), $code ) ) . "\n";
		} else {
			/* translators: %s code */
			echo '<p style="font-size:16px"><strong>' . esc_html( sprintf( __( 'RM5 off your next Bunny Babe order: %s', 'bunnybabe' ), $code ) ) . '</strong></p>';
		}
	},
	10,
	3
);

/* ------------------------------------------------------------------
 * Voucher leads (WhatsApp numbers from "Claim voucher").
 * ------------------------------------------------------------------ */
add_action(
	'init',
	function () {
		register_post_type(
			'bb_lead',
			array(
				'labels'          => array(
					'name'          => __( 'Leads', 'bunnybabe' ),
					'singular_name' => __( 'Lead', 'bunnybabe' ),
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => 'woocommerce',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}
);

add_filter(
	'manage_bb_lead_posts_columns',
	function ( $cols ) {
		return array(
			'cb'        => isset( $cols['cb'] ) ? $cols['cb'] : '',
			'title'     => __( 'WhatsApp number', 'bunnybabe' ),
			'bb_name'   => __( 'Name', 'bunnybabe' ),
			'bb_status' => __( 'Status', 'bunnybabe' ),
			'bb_bag'    => __( 'Bag / address', 'bunnybabe' ),
			'bb_wa'     => __( 'Chat', 'bunnybabe' ),
			'bb_seen'   => __( 'Last seen', 'bunnybabe' ),
		);
	}
);
add_action(
	'manage_bb_lead_posts_custom_column',
	function ( $col, $post_id ) {
		$m = function ( $k ) use ( $post_id ) {
			return (string) get_post_meta( $post_id, '_bb_' . $k, true );
		};
		switch ( $col ) {
			case 'bb_name':
				echo esc_html( $m( 'name' ) ? $m( 'name' ) : '—' );
				if ( $m( 'email' ) ) {
					echo '<br><small>' . esc_html( $m( 'email' ) ) . '</small>';
				}
				break;
			case 'bb_status':
				$st = $m( 'status' );
				if ( 'ordered' === $st ) {
					echo '<strong style="color:#008a20">' . esc_html( sprintf( /* translators: %s order number */ __( 'Ordered #%s', 'bunnybabe' ), $m( 'order' ) ) ) . '</strong>';
				} elseif ( 'checkout' === $st ) {
					echo '<strong style="color:#c4007a">' . esc_html__( 'At checkout — not paid', 'bunnybabe' ) . '</strong>';
				} else {
					echo esc_html__( 'Claimed voucher', 'bunnybabe' );
				}
				break;
			case 'bb_bag':
				echo esc_html( $m( 'bag' ) ? $m( 'bag' ) : '—' );
				$addr = trim( implode( ', ', array_filter( array( $m( 'address' ), $m( 'postcode' ) . ' ' . $m( 'city' ), $m( 'state' ) ) ) ), ', ' );
				if ( $addr ) {
					echo '<br><small>' . esc_html( $addr ) . '</small>';
				}
				break;
			case 'bb_wa':
				$num = bb_normalize_phone( get_the_title( $post_id ) );
				if ( $num ) {
					echo '<a href="' . esc_url( 'https://wa.me/' . $num ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open WhatsApp', 'bunnybabe' ) . '</a>';
				}
				break;
			case 'bb_seen':
				$seen = $m( 'seen' );
				echo esc_html( $seen ? mysql2date( 'j M, g:i A', $seen ) : get_the_date( 'j M, g:i A', $post_id ) );
				break;
		}
	},
	10,
	2
);

/** Apply the claimed voucher once the bag has something in it. */
function bb_maybe_apply_pending_coupon() {
	if ( ! WC()->session || ! WC()->cart ) {
		return;
	}
	$code = WC()->session->get( 'bb_pending_coupon' );
	if ( ! $code || WC()->cart->is_empty() ) {
		return;
	}
	if ( ! WC()->cart->has_discount( $code ) ) {
		WC()->cart->apply_coupon( $code );
	}
	WC()->session->set( 'bb_pending_coupon', null );
}
add_action( 'woocommerce_add_to_cart', 'bb_maybe_apply_pending_coupon', 99 );

function bb_ajax_claim() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- public lead form; honeypot + rate limit below.
	if ( ! empty( $_POST['website'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Something went wrong.', 'bunnybabe' ) ) );
	}
	$raw   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	// phpcs:enable
	$phone = bb_normalize_phone( $raw );
	if ( strlen( $phone ) < 10 || strlen( $phone ) > 13 ) {
		wp_send_json_error( array( 'message' => __( 'Enter a valid phone number to claim.', 'bunnybabe' ) ) );
	}

	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'bb_claim_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit > 10 ) {
		wp_send_json_error( array( 'message' => __( 'Too many tries. Please wait a bit.', 'bunnybabe' ) ) );
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$exists = get_posts(
		array(
			'post_type'      => 'bb_lead',
			'title'          => $phone,
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( empty( $exists ) ) {
		wp_insert_post(
			array(
				'post_type'   => 'bb_lead',
				'post_title'  => $phone,
				'post_status' => 'publish',
			)
		);
	}

	$bag = null;
	if ( WC()->session && WC()->cart ) {
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
		WC()->session->set( 'bb_pending_coupon', bb_config()['first_coupon'] );
		bb_maybe_apply_pending_coupon();
		$bag = bb_bag_state();
	}

	wp_send_json_success(
		array(
			'code' => strtoupper( bb_config()['first_coupon'] ),
			'bag'  => $bag,
		)
	);
}
add_action( 'wp_ajax_bb_claim', 'bb_ajax_claim' );
add_action( 'wp_ajax_nopriv_bb_claim', 'bb_ajax_claim' );

/* ------------------------------------------------------------------
 * Express checkout (v1.2): auto vouchers, email optional, saved address.
 * ------------------------------------------------------------------ */

/** Hide WooCommerce's "Coupon applied" notice when we apply one for the customer. */
add_filter(
	'woocommerce_coupon_message',
	function ( $msg ) {
		return empty( $GLOBALS['bb_auto_coupon'] ) ? $msg : '';
	}
);

/** Apply / remove a coupon quietly. */
function bb_quiet_coupon( $code, $add = true ) {
	$GLOBALS['bb_auto_coupon'] = true;
	if ( $add ) {
		$ok = WC()->cart->apply_coupon( $code );
	} else {
		$ok = WC()->cart->remove_coupon( $code );
	}
	$GLOBALS['bb_auto_coupon'] = false;
	return $ok;
}

/** Customer removed a voucher themselves → stop auto-applying this visit. */
add_action(
	'woocommerce_removed_coupon',
	function () {
		if ( empty( $GLOBALS['bb_auto_coupon'] ) && WC()->session ) {
			WC()->session->set( 'bb_no_auto', 1 );
		}
	}
);

/** An unused second-order code for this phone/email, or ''. */
function bb_find_next_voucher( $email, $phone ) {
	$phone = bb_normalize_phone( $phone );
	$ids   = array();
	if ( $phone ) {
		$ids = wc_get_orders(
			array(
				'limit'      => 10,
				'return'     => 'ids',
				'status'     => bb_paid_statuses(),
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'   => '_bb_phone',
						'value' => $phone,
					),
				),
			)
		);
	}
	if ( ! $ids && $email ) {
		$ids = wc_get_orders(
			array(
				'limit'         => 10,
				'return'        => 'ids',
				'status'        => bb_paid_statuses(),
				'billing_email' => $email,
			)
		);
	}
	foreach ( $ids as $id ) {
		$order = wc_get_order( $id );
		$code  = $order ? bb_next_voucher_code( $order ) : '';
		if ( ! $code ) {
			continue;
		}
		$coupon = new WC_Coupon( $code );
		if ( $coupon->get_id() && $coupon->get_usage_count() < max( 1, (int) $coupon->get_usage_limit() ) ) {
			return $code;
		}
	}
	return '';
}

/** True when the bag only holds the hidden RM1 test product (no auto vouchers then). */
function bb_cart_is_test() {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return false;
	}
	foreach ( WC()->cart->get_cart() as $item ) {
		if ( ! get_post_meta( $item['product_id'], '_bb_test_product', true ) ) {
			return false;
		}
	}
	return true;
}

/** Auto-apply RM5 for first orders once the bag has enough bottles. */
function bb_auto_first_coupon() {
	if ( ! WC()->session || ! WC()->cart || WC()->session->get( 'bb_no_auto' ) || WC()->session->get( 'bb_returning' ) || bb_cart_is_test() ) {
		return;
	}
	if ( WC()->customer && bb_customer_has_orders( WC()->customer->get_billing_email(), WC()->customer->get_billing_phone() ) ) {
		WC()->session->set( 'bb_returning', 1 );
		return;
	}
	if ( ! WC()->cart->get_applied_coupons() && bb_cart_scent_count() >= (int) bb_config()['first_min'] ) {
		bb_quiet_coupon( bb_config()['first_coupon'] );
	}
}
add_action( 'woocommerce_add_to_cart', 'bb_auto_first_coupon', 100 );

/** Bag changed (+/−, remove): drop vouchers that no longer fit, add BUNNY5 when it now does. */
function bb_sync_bag_coupons() {
	if ( ! WC()->cart ) {
		return;
	}
	$discounts = new WC_Discounts( WC()->cart );
	foreach ( WC()->cart->get_applied_coupons() as $code ) {
		if ( is_wp_error( $discounts->is_coupon_valid( new WC_Coupon( $code ) ) ) ) {
			bb_quiet_coupon( $code, false );
		}
	}
	bb_auto_first_coupon();
}

/** BUNNY5 needs at least 2 bottles in the bag. */
add_filter(
	'woocommerce_coupon_is_valid',
	function ( $valid, $coupon ) {
		$min = (int) bb_config()['first_min'];
		if ( $valid && 0 === strcasecmp( $coupon->get_code(), bb_config()['first_coupon'] ) && bb_cart_scent_count() < $min ) {
			throw new Exception(
				esc_html(
					sprintf(
						/* translators: 1 code, 2 bottles */
						__( '%1$s works from %2$d bottles. Add one more scent to use it ♡', 'bunnybabe' ),
						strtoupper( bb_config()['first_coupon'] ),
						$min
					)
				)
			);
		}
		return $valid;
	},
	10,
	2
);

/**
 * Never let a voucher block checkout. Before WooCommerce checks the bag,
 * quietly drop any voucher that is no longer valid for this customer and,
 * for returning customers, swap in their own second-order voucher.
 */
add_action(
	'woocommerce_check_cart_items',
	function () {
		if ( ! WC()->cart || WC()->cart->is_empty() ) {
			return;
		}
		$email = WC()->customer ? WC()->customer->get_billing_email() : '';
		$phone = WC()->customer ? WC()->customer->get_billing_phone() : '';
		$first = bb_config()['first_coupon'];
		if ( WC()->cart->has_discount( $first ) && bb_customer_has_orders( $email, $phone ) ) {
			bb_quiet_coupon( $first, false );
			if ( WC()->session ) {
				WC()->session->set( 'bb_returning', 1 );
			}
		}
		$discounts = new WC_Discounts( WC()->cart );
		foreach ( WC()->cart->get_applied_coupons() as $code ) {
			$valid = $discounts->is_coupon_valid( new WC_Coupon( $code ) );
			if ( is_wp_error( $valid ) ) {
				bb_quiet_coupon( $code, false );
			}
		}
		if ( ! WC()->cart->get_applied_coupons() && ! bb_cart_is_test() && WC()->session && ! WC()->session->get( 'bb_no_auto' ) && WC()->session->get( 'bb_returning' ) ) {
			$next = bb_find_next_voucher( $email, $phone );
			if ( $next && ! is_wp_error( $discounts->is_coupon_valid( new WC_Coupon( $next ) ) ) ) {
				bb_quiet_coupon( $next );
			}
		}
	},
	0
);

/** Quietly-removed vouchers should not show WooCommerce's "coupon removed" error either. */
add_filter(
	'woocommerce_coupon_error',
	function ( $msg ) {
		return empty( $GLOBALS['bb_auto_coupon'] ) ? $msg : '';
	}
);

/** At checkout, once we know the phone: swap to the right voucher. */
add_action(
	'woocommerce_checkout_update_order_review',
	function ( $raw ) {
		if ( ! WC()->cart || WC()->cart->is_empty() || ! WC()->session ) {
			return;
		}
		if ( bb_cart_is_test() ) {
			foreach ( WC()->cart->get_applied_coupons() as $c ) {
				bb_quiet_coupon( $c, false );
			}
			return;
		}
		parse_str( (string) $raw, $data );
		$phone = isset( $data['billing_phone'] ) ? bb_normalize_phone( sanitize_text_field( $data['billing_phone'] ) ) : '';
		$email = isset( $data['billing_email'] ) ? sanitize_email( $data['billing_email'] ) : '';
		if ( strlen( $phone ) < 10 && ! $email ) {
			return;
		}
		$first = bb_config()['first_coupon'];
		if ( bb_customer_has_orders( $email, $phone ) ) {
			WC()->session->set( 'bb_returning', 1 );
			if ( WC()->cart->has_discount( $first ) ) {
				bb_quiet_coupon( $first, false );
			}
			$next = bb_find_next_voucher( $email, $phone );
			if ( $next && ! WC()->cart->has_discount( $next ) && ! WC()->session->get( 'bb_no_auto' ) ) {
				bb_quiet_coupon( $next );
				wc_add_notice( sprintf( /* translators: %s code */ __( 'Welcome back! Your RM5 voucher %s is applied ♡', 'bunnybabe' ), $next ), 'success' );
			}
		} else {
			WC()->session->set( 'bb_returning', 0 );
			foreach ( WC()->cart->get_applied_coupons() as $c ) {
				if ( 0 === stripos( $c, 'bb' ) && strlen( $c ) === 8 ) {
					bb_quiet_coupon( $c, false );
				}
			}
			if ( ! WC()->cart->get_applied_coupons() && ! WC()->session->get( 'bb_no_auto' ) ) {
				bb_quiet_coupon( $first );
			}
		}
	}
);

/** Second-order vouchers belong to the phone/email of the first order. */
add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		if ( ! WC()->cart ) {
			return;
		}
		$email = isset( $data['billing_email'] ) ? $data['billing_email'] : '';
		$phone = isset( $data['billing_phone'] ) ? $data['billing_phone'] : '';
		foreach ( WC()->cart->get_applied_coupons() as $code ) {
			if ( 0 !== stripos( $code, 'bb' ) || strlen( $code ) !== 8 ) {
				continue;
			}
			if ( strtolower( bb_find_next_voucher( $email, $phone ) ) !== strtolower( $code ) ) {
				WC()->cart->remove_coupon( $code );
				$errors->add( 'bb_next_owner', __( 'That RM5 voucher belongs to another customer, so we removed it.', 'bunnybabe' ) );
			}
		}
	},
	11,
	2
);

/** Second-order codes: no email lock (email is optional now); ownership is checked above. */
add_filter(
	'woocommerce_coupon_get_email_restrictions',
	function ( $emails, $coupon ) {
		$code = $coupon->get_code();
		return ( 0 === stripos( $code, 'bb' ) && strlen( $code ) === 8 ) ? array() : $emails;
	},
	10,
	2
);

/* ---------- Saved address for returning customers (any device) ---------- */

/** Mask a string: keep the first 2 characters of each word. */
function bb_mask( $s ) {
	return preg_replace_callback(
		'/\p{L}[\p{L}\p{M}]*/u',
		function ( $m ) {
			$w = $m[0];
			$n = mb_strlen( $w );
			return $n <= 2 ? $w : mb_substr( $w, 0, 2 ) . str_repeat( '•', min( 4, $n - 2 ) );
		},
		(string) $s
	);
}

/** Last paid order for a phone. */
function bb_last_order_for_phone( $phone ) {
	$phone = bb_normalize_phone( $phone );
	if ( strlen( $phone ) < 10 ) {
		return null;
	}
	$ids = wc_get_orders(
		array(
			'limit'      => 1,
			'return'     => 'ids',
			'status'     => bb_paid_statuses(),
			'orderby'    => 'date',
			'order'      => 'DESC',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'   => '_bb_phone',
					'value' => $phone,
				),
			),
		)
	);
	return $ids ? wc_get_order( $ids[0] ) : null;
}

/** AJAX: masked saved address for a phone. Real values never leave the server. */
function bb_ajax_lookup() {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- returns masked data only; rate limited.
	$phone = bb_normalize_phone( isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '' );
	// phpcs:enable
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x';
	$key = 'bb_look_' . md5( $ip );
	$hit = (int) get_transient( $key );
	if ( $hit > 20 ) {
		wp_send_json_error();
	}
	set_transient( $key, $hit + 1, HOUR_IN_SECONDS );

	$order = bb_last_order_for_phone( $phone );
	if ( ! $order || ! $order->get_billing_address_1() ) {
		if ( WC()->session ) {
			WC()->session->set( 'bb_saved', null );
		}
		wp_send_json_success( array( 'found' => false ) );
	}
	if ( WC()->session ) {
		if ( ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
		WC()->session->set(
			'bb_saved',
			array(
				'phone' => $phone,
				'order' => $order->get_id(),
			)
		);
	}
	$states = WC()->countries->get_states( 'MY' );
	$st     = $order->get_billing_state();
	wp_send_json_success(
		array(
			'found'    => true,
			'name'     => bb_mask( $order->get_billing_first_name() ),
			'address'  => bb_mask( $order->get_billing_address_1() ),
			'postcode' => substr( (string) $order->get_billing_postcode(), 0, 2 ) . '•••',
			'city'     => bb_mask( $order->get_billing_city() ),
			'state'    => $st,
			'state_name' => isset( $states[ $st ] ) ? $states[ $st ] : $st,
		)
	);
}
add_action( 'wp_ajax_bb_lookup', 'bb_ajax_lookup' );
add_action( 'wp_ajax_nopriv_bb_lookup', 'bb_ajax_lookup' );

/** When "use saved address" was chosen, fill name/address from the last order on the server. */
add_filter(
	'woocommerce_checkout_posted_data',
	function ( $data ) {
		if ( empty( $_POST['bb_use_saved'] ) || ! WC()->session ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce checks the checkout nonce.
			return $data;
		}
		$saved = WC()->session->get( 'bb_saved' );
		$phone = bb_normalize_phone( isset( $data['billing_phone'] ) ? $data['billing_phone'] : '' );
		if ( ! $saved || $saved['phone'] !== $phone ) {
			return $data;
		}
		$order = wc_get_order( $saved['order'] );
		if ( ! $order || bb_normalize_phone( $order->get_billing_phone() ) !== $phone ) {
			return $data;
		}
		$data['billing_first_name'] = $order->get_billing_first_name();
		$data['billing_address_1']  = $order->get_billing_address_1();
		$data['billing_postcode']   = $order->get_billing_postcode();
		$data['billing_city']       = $order->get_billing_city();
		$data['billing_state']      = $order->get_billing_state();
		// Anyone can type a phone number, so the receipt (which shows the full
		// address) only ever goes to the email already on that saved order.
		$data['billing_email'] = $order->get_billing_email();
		if ( ! empty( $data['ship_to_different_address'] ) ) {
			return $data;
		}
		foreach ( array( 'first_name', 'address_1', 'postcode', 'city', 'state' ) as $f ) {
			$data[ 'shipping_' . $f ] = $data[ 'billing_' . $f ];
		}
		return $data;
	}
);
