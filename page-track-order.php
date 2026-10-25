<?php
/**
 * Track order page (slug: track-order).
 */
defined( 'ABSPATH' ) || exit;

$bb_result = null;
$bb_number = '';
$bb_phone  = '';
// phpcs:disable WordPress.Security.NonceVerification.Missing -- read-only lookup protected by order number + phone match.
if ( 'POST' === ( isset( $_SERVER['REQUEST_METHOD'] ) ? $_SERVER['REQUEST_METHOD'] : '' ) && isset( $_POST['bb_track'] ) && function_exists( 'bb_track_lookup' ) ) {
	$bb_number = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : '';
	$bb_phone  = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$bb_result = bb_track_lookup( $bb_number, $bb_phone );
}
// phpcs:enable

// Demo mode: /track-order/?demo=1&step=0..4 shows a sample order (no real data).
// phpcs:disable WordPress.Security.NonceVerification.Recommended
$bb_demo = isset( $_GET['demo'] );
$bb_demo_step = $bb_demo && isset( $_GET['step'] ) ? max( 0, min( 4, absint( $_GET['step'] ) ) ) : 2;
// phpcs:enable

$bb_view = null;
if ( $bb_result instanceof WC_Order ) {
	$order    = $bb_result;
	$tracking = (string) $order->get_meta( '_bb_tracking_no' );
	$tpl      = (string) get_theme_mod( 'bb_tracking_url', '' );
	$items    = array();
	foreach ( $order->get_items() as $item ) {
		$scent   = bb_scent_key_for_product( $item->get_product_id() );
		$items[] = array(
			'name' => $item->get_name(),
			'qty'  => $item->get_quantity(),
			'img'  => $scent ? bb_asset( 'img/' . bb_config()['scents'][ $scent ]['bottle'] ) : '',
		);
	}
	$totals = array();
	foreach ( $order->get_order_item_totals() as $key => $row ) {
		if ( 'payment_method' !== $key ) {
			$totals[] = array( wp_strip_all_tags( $row['label'] ), $row['value'], 'order_total' === $key );
		}
	}
	$bb_view = array(
		'number'   => $order->get_order_number(),
		'step'     => bb_track_step( $order ),
		'status'   => wc_get_order_status_name( $order->get_status() ),
		'dates'    => bb_track_dates( $order ),
		'courier'  => (string) $order->get_meta( '_bb_courier' ),
		'tracking' => $tracking,
		'link'     => ( $tpl && $tracking ) ? str_replace( '{tracking}', rawurlencode( $tracking ), $tpl ) : '',
		'items'    => $items,
		'totals'   => $totals,
	);
} elseif ( $bb_demo ) {
	$cfg   = bb_config();
	$now   = current_time( 'timestamp' );
	$dates = array();
	foreach ( array( 3, 2.6, 1.5, 0.4, 0 ) as $i => $days_ago ) {
		$dates[ $i ] = $i <= $bb_demo_step ? date_i18n( 'j M, g:i A', (int) ( $now - $days_ago * DAY_IN_SECONDS ) ) : '';
	}
	$bb_view = array(
		'number'   => '1024',
		'step'     => $bb_demo_step,
		'status'   => '',
		'dates'    => $dates,
		'courier'  => $bb_demo_step >= 2 ? 'J&T Express' : '',
		'tracking' => $bb_demo_step >= 2 ? 'JT6012345678' : '',
		'link'     => '',
		'items'    => array(
			array( 'name' => 'Bunny Babe Secret EDP 30ml', 'qty' => 1, 'img' => bb_asset( 'img/' . $cfg['scents']['secret']['bottle'] ) ),
			array( 'name' => 'Bunny Babe Marsh EDP 30ml', 'qty' => 1, 'img' => bb_asset( 'img/' . $cfg['scents']['marsh']['bottle'] ) ),
		),
		'totals'   => array(
			array( __( 'Subtotal:', 'bunnybabe' ), 'RM78.00', false ),
			array( __( 'Shipment:', 'bunnybabe' ), __( 'FREE shipping', 'bunnybabe' ), false ),
			array( __( 'Bundle savings:', 'bunnybabe' ), '-RM19.00', false ),
			array( __( 'Total:', 'bunnybabe' ), 'RM59.00', true ),
		),
	);
}

get_header();

$bb_icons = array(
	'<path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/>',
	'<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4"/><path d="M12 11v10"/>',
	'<rect x="2" y="7" width="12" height="9" rx="1"/><path d="M14 10h4l3 3v3h-7z"/><circle cx="6.5" cy="18" r="2"/><circle cx="17" cy="18" r="2"/>',
	'<path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
	'<path d="M3 11l9-7 9 7v9H3z"/><path d="M9 20v-6h6v6"/>',
);
$bb_titles = array(
	__( 'Order placed', 'bunnybabe' ),
	__( 'Packed with love', 'bunnybabe' ),
	__( 'Shipped', 'bunnybabe' ),
	__( 'Out for delivery', 'bunnybabe' ),
	__( 'Delivered', 'bunnybabe' ),
);
$bb_heads = array(
	__( 'Order received!', 'bunnybabe' ),
	__( 'Getting it ready', 'bunnybabe' ),
	__( 'On the way!', 'bunnybabe' ),
	__( 'Almost there!', 'bunnybabe' ),
	__( 'Delivered ♡', 'bunnybabe' ),
);
?>
<section class="bb-page">
	<div class="bb-wrap">
		<div class="bb-track__top">
			<div class="bb-track__intro">
				<span class="bb-sticker"><?php esc_html_e( "where's my babe? ♡", 'bunnybabe' ); ?></span>
				<h1 class="bb-hero__title" style="font-size:clamp(64px,8vw,120px)"><?php esc_html_e( 'Track your order', 'bunnybabe' ); ?></h1>
				<p class="bb-lead"><?php esc_html_e( 'Enter your order number and the phone number you used at checkout. Your order number is in your confirmation email.', 'bunnybabe' ); ?></p>
			</div>
			<form class="bb-track__form" method="post" action="">
				<div class="bb-field">
					<label class="bb-label" for="bb-order"><?php esc_html_e( 'Order number', 'bunnybabe' ); ?></label>
					<input class="bb-input" id="bb-order" name="order" type="text" inputmode="numeric" placeholder="e.g. 1024" value="<?php echo esc_attr( $bb_number ); ?>" required>
				</div>
				<div class="bb-field">
					<label class="bb-label" for="bb-phone"><?php esc_html_e( 'Phone number', 'bunnybabe' ); ?></label>
					<input class="bb-input" id="bb-phone" name="phone" type="tel" placeholder="012-345 6789" value="<?php echo esc_attr( $bb_phone ); ?>" required>
				</div>
				<?php if ( is_wp_error( $bb_result ) ) : ?>
					<span class="bb-msg" role="alert"><?php echo esc_html( $bb_result->get_error_message() ); ?></span>
				<?php endif; ?>
				<button class="bb-btn bb-btn--black" type="submit" name="bb_track" value="1"><?php esc_html_e( 'Track order', 'bunnybabe' ); ?></button>
				<a href="<?php echo esc_url( bb_whatsapp_url() ); ?>" style="font-size:14px;font-weight:600;text-align:center"><?php esc_html_e( "Can't find your order number? WhatsApp us", 'bunnybabe' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( 'demo', 1, bb_track_url() ) ); ?>" style="font-size:13px;text-align:center;color:var(--bb-ink-3)"><?php esc_html_e( 'See how tracking looks (demo)', 'bunnybabe' ); ?></a>
			</form>
		</div>

		<?php if ( $bb_demo ) : ?>
			<div class="bb-demo">
				<strong><?php esc_html_e( 'Demo order — sample data only', 'bunnybabe' ); ?></strong>
				<div class="bb-demo__steps">
					<?php foreach ( $bb_titles as $i => $t ) : ?>
						<a class="bb-chip<?php echo $i === $bb_demo_step ? ' is-on' : ''; ?>" href="<?php echo esc_url( add_query_arg( array( 'demo' => 1, 'step' => $i ), bb_track_url() ) ); ?>"><?php echo esc_html( $t ); ?></a>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php
		if ( $bb_view ) :
			$step     = $bb_view['step'];
			$dates    = $bb_view['dates'];
			$courier  = $bb_view['courier'];
			$tracking = $bb_view['tracking'];
			$link     = $bb_view['link'];
			?>
			<div class="bb-track__result">
				<div class="bb-status">
					<div class="bb-status__head">
						<div>
							<small><?php echo esc_html( sprintf( /* translators: %s number */ __( 'ORDER #%s', 'bunnybabe' ), $bb_view['number'] ) ); ?></small>
							<strong><?php echo esc_html( $step >= 0 ? $bb_heads[ $step ] : $bb_view['status'] ); ?></strong>
						</div>
					</div>

					<?php if ( $step >= 0 ) : ?>
						<?php $pct = (int) round( $step / 4 * 100 ); ?>
						<div class="bb-progress">
							<div class="bb-progress__labels"><span><?php esc_html_e( 'Progress', 'bunnybabe' ); ?></span><span><?php echo esc_html( $bb_titles[ $step ] ); ?></span></div>
							<div class="bb-progress__bar" role="progressbar" aria-label="<?php esc_attr_e( 'Delivery progress', 'bunnybabe' ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $pct ); ?>">
								<div class="bb-progress__fill" style="width:<?php echo esc_attr( max( 4, $pct ) ); ?>%">
									<span class="bb-progress__truck"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FF5CB8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $bb_icons[2]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></svg></span>
								</div>
							</div>
						</div>
						<ol class="bb-steps">
							<?php foreach ( $bb_titles as $i => $title ) : ?>
								<?php
								$cls = $i < $step ? 'is-done' : ( $i === $step ? 'is-current' : '' );
								if ( 4 === $step && 4 === $i ) {
									$cls = 'is-done';
								}
								$detail = ! empty( $dates[ $i ] ) && $i <= $step ? $dates[ $i ] : ( $i <= $step ? __( 'Done', 'bunnybabe' ) : __( 'Coming soon', 'bunnybabe' ) );
								?>
								<li class="bb-step <?php echo esc_attr( $cls ); ?>">
									<div class="bb-step__rail">
										<span class="bb-step__dot" style="animation-delay:<?php echo esc_attr( $i * 0.25 ); ?>s"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $bb_icons[ $i ]; // phpcs:ignore ?></svg></span>
										<?php if ( $i < 4 ) : ?>
											<span class="bb-step__line"><?php if ( $i < $step ) : ?><span style="animation-delay:<?php echo esc_attr( $i * 0.25 + 0.2 ); ?>s"></span><?php endif; ?></span>
										<?php endif; ?>
									</div>
									<div class="bb-step__text"><strong><?php echo esc_html( $title ); ?></strong><span><?php echo esc_html( $detail ); ?></span></div>
								</li>
							<?php endforeach; ?>
						</ol>
					<?php else : ?>
						<p style="padding:24px 32px;margin:0"><?php esc_html_e( 'This order is not active. WhatsApp us if you need help.', 'bunnybabe' ); ?></p>
					<?php endif; ?>

					<div class="bb-courier">
						<div>
							<small><?php esc_html_e( 'COURIER', 'bunnybabe' ); ?></small>
							<span><?php echo esc_html( trim( 'EasyParcel · ' . ( $courier ? $courier : __( 'to be assigned', 'bunnybabe' ) ) . ( $tracking ? ' · ' . $tracking : '' ) ) ); ?></span>
						</div>
						<?php if ( $link ) : ?>
							<a class="bb-btn bb-btn--white bb-btn--sm" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Track on courier site', 'bunnybabe' ); ?></a>
						<?php endif; ?>
					</div>
				</div>

				<div class="bb-side">
					<div class="bb-box">
						<span class="bb-h3"><?php esc_html_e( 'Your order', 'bunnybabe' ); ?></span>
						<?php foreach ( $bb_view['items'] as $it ) : ?>
							<div class="bb-bag__item">
								<?php if ( $it['img'] ) : ?><img src="<?php echo esc_url( $it['img'] ); ?>" alt="" width="60" height="60"><?php endif; ?>
								<div class="bb-bag__meta"><strong><?php echo esc_html( $it['name'] ); ?></strong><span>× <?php echo esc_html( $it['qty'] ); ?></span></div>
							</div>
						<?php endforeach; ?>
						<div class="bb-lines">
							<?php foreach ( $bb_view['totals'] as $row ) : ?>
								<div class="<?php echo $row[2] ? 'is-total' : ''; ?>"><strong style="font-weight:<?php echo $row[2] ? 700 : 400; ?>"><?php echo esc_html( $row[0] ); ?></strong><strong><?php echo wp_kses_post( $row[1] ); ?></strong></div>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="bb-box bb-box--dark">
						<span class="bb-h3"><?php esc_html_e( 'Need help?', 'bunnybabe' ); ?></span>
						<p><?php esc_html_e( 'Questions about your parcel? Our team replies on WhatsApp.', 'bunnybabe' ); ?></p>
						<a class="bb-btn bb-btn--pink" href="<?php echo esc_url( bb_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp us', 'bunnybabe' ); ?></a>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
