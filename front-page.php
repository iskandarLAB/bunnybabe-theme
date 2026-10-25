<?php
/**
 * Front page: one-page shop + checkout.
 */
defined( 'ABSPATH' ) || exit;
get_header();

$bb       = bb_config();
$has_woo  = class_exists( 'WooCommerce' ) && WC()->cart;
$is_empty = $has_woo ? WC()->cart->is_empty() : true;
$n        = $has_woo ? bb_cart_scent_count() : 0;
$qtys     = array();
if ( $has_woo ) {
	foreach ( WC()->cart->get_cart() as $item ) {
		$qtys[ (int) $item['product_id'] ] = ( isset( $qtys[ (int) $item['product_id'] ] ) ? $qtys[ (int) $item['product_id'] ] : 0 ) + (int) $item['quantity'];
	}
}
$icon_bag   = '<svg class="bb-wiggle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>';
$icon_arrow = '<svg class="bb-nudge" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M13 6l6 6-6 6"/></svg>';
$aff_url    = get_theme_mod( 'bb_affiliate_url', '' );
?>

<!-- Hero -->
<section class="bb-wrap bb-hero" id="top">
	<div class="bb-hero__copy">
		<span class="bb-sticker bb-up">cute n chaos ♡</span>
		<h1 class="bb-hero__title bb-up" style="animation-delay:.1s"><?php esc_html_e( 'Cute outside.', 'bunnybabe' ); ?><br><?php esc_html_e( 'Chaos inside.', 'bunnybabe' ); ?></h1>
		<p class="bb-hero__lead bb-up" style="animation-delay:.2s"><?php esc_html_e( 'Affordable eau de parfum for everyday girls. Five scents, one for every mood. 30ml bottles at RM39 each.', 'bunnybabe' ); ?></p>
		<div class="bb-hero__ctas bb-up" style="animation-delay:.3s">
			<a class="bb-btn bb-btn--black" href="#scents"><?php esc_html_e( 'Shop the scents', 'bunnybabe' ); ?></a>
			<a class="bb-btn bb-btn--pink" href="#bundles"><?php esc_html_e( 'See bundles', 'bunnybabe' ); ?></a>
		</div>
		<div class="bb-hero__facts bb-up" style="animation-delay:.4s">
			<span><?php esc_html_e( 'Eau de Parfum', 'bunnybabe' ); ?></span>
			<span>30ml / 1.0 fl oz</span>
			<span><?php esc_html_e( '5 scents', 'bunnybabe' ); ?></span>
			<span><?php esc_html_e( 'Save up to RM96', 'bunnybabe' ); ?></span>
			<span><?php esc_html_e( 'FREE shipping 2+ bottles', 'bunnybabe' ); ?></span>
		</div>
	</div>
	<div class="bb-hero__media">
		<div class="bb-hero__frame bb-float-in">
			<img src="<?php echo esc_url( bb_asset( 'img/hero-girl.jpg' ) ); ?>" alt="<?php esc_attr_e( 'Girl in a pink hoodie holding a Bunny Babe perfume bottle', 'bunnybabe' ); ?>" width="752" height="1344" fetchpriority="high">
			<svg class="bb-hero__sparkle bb-twinkle" width="56" height="56" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z" fill="#fff" stroke="#111" stroke-width="1.2"/></svg>
			<svg class="bb-hero__heart bb-beat" width="40" height="40" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2A5 5 0 0 1 12 6.4a5 5 0 0 1 9.6 5.4C19.5 16.4 12 21 12 21z" fill="#fff" stroke="#111" stroke-width="1.4"/></svg>
		</div>
		<div class="bb-hero__price bb-wobble"><strong>RM39</strong><span>30ML EDP</span></div>
		<div class="bb-hero__tag bb-sway"><?php esc_html_e( 'Smell cute. Do chaos.', 'bunnybabe' ); ?></div>
	</div>
</section>

<!-- Marquee -->
<div class="bb-marquee" aria-hidden="true">
	<div class="bb-marquee__track">
		<?php for ( $g = 0; $g < 2; $g++ ) : ?>
			<div class="bb-marquee__group">
				<?php
				for ( $r = 0; $r < 2; $r++ ) {
					foreach ( $bb['scents'] as $s ) {
						echo '<span>' . esc_html( $s['name'] ) . '</span><i>♡</i>';
					}
				}
				?>
			</div>
		<?php endfor; ?>
	</div>
</div>

<!-- Bundles -->
<section class="bb-bundles" id="bundles">
	<div class="bb-wrap bb-bundles__inner">
		<div class="bb-center">
			<span class="bb-eyebrow"><?php esc_html_e( 'Bundle & save', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'More bottles, more moods', 'bunnybabe' ); ?></h2>
			<p class="bb-lead" style="color:#111"><?php esc_html_e( 'Mix any scents you like. Bundle prices apply automatically in your bag. The more you grab, the less you pay per bottle.', 'bunnybabe' ); ?></p>
		</div>
		<div class="bb-plans">
			<div class="bb-plan bb-lift bb-reveal">
				<div><div class="bb-plan__name">SOLO</div><div class="bb-plan__sub"><?php esc_html_e( '1 bottle · 30ml', 'bunnybabe' ); ?></div></div>
				<div class="bb-plan__price"><strong>RM39</strong></div>
				<ul><li><?php esc_html_e( 'Any one scent', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Perfect first try', 'bunnybabe' ); ?></li><li><?php esc_html_e( '+ RM5 shipping', 'bunnybabe' ); ?></li></ul>
				<a class="bb-btn bb-btn--white bb-cta" href="#scents" data-bb-open="vanilla" data-qty="1"><span><?php esc_html_e( 'Pick 1 scent', 'bunnybabe' ); ?></span><?php echo $icon_arrow; // phpcs:ignore ?></a>
			</div>
			<div class="bb-plan bb-lift bb-reveal">
				<span class="bb-plan__tag bb-sway2"><?php esc_html_e( 'jimat RM19 ♡', 'bunnybabe' ); ?></span>
				<div><div class="bb-plan__name">DUO</div><div class="bb-plan__sub"><?php esc_html_e( '2 bottles · 30ml each', 'bunnybabe' ); ?></div></div>
				<div class="bb-plan__price"><strong>RM59</strong><s>RM78</s></div>
				<ul><li><?php esc_html_e( 'RM29.50 per bottle', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Save RM19', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'FREE shipping', 'bunnybabe' ); ?></li></ul>
				<a class="bb-btn bb-btn--black bb-cta bb-cta--pinkshadow" href="#scents" data-bb-open="vanilla" data-qty="2"><span><?php esc_html_e( 'Pick 2 scents', 'bunnybabe' ); ?></span><?php echo $icon_arrow; // phpcs:ignore ?></a>
			</div>
			<div class="bb-plan bb-plan--hero bb-lift bb-reveal">
				<span class="bb-plan__tag bb-sway2"><?php esc_html_e( 'best value ♡', 'bunnybabe' ); ?></span>
				<div><div class="bb-plan__name">BABE PACK</div><div class="bb-plan__sub"><?php esc_html_e( '5 bottles · 30ml each', 'bunnybabe' ); ?></div></div>
				<div class="bb-countdown" data-bb-countdown="586" role="timer" aria-live="off" hidden><?php esc_html_e( 'Deal ends in', 'bunnybabe' ); ?> <strong data-bb="cd-time">09:46</strong></div>
				<div class="bb-plan__price"><strong>RM99</strong><s>RM195</s></div>
				<ul><li><?php esc_html_e( 'RM19.80 per bottle', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Save RM96', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'FREE shipping', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Collect all 5 scents', 'bunnybabe' ); ?></li><li><?php esc_html_e( '+ FREE mystery tester 5ml', 'bunnybabe' ); ?></li></ul>
				<button type="button" class="bb-btn bb-btn--pink bb-cta bb-cta--light" data-bb-addall>
					<svg class="bb-spin" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z" fill="#fff" stroke="#111" stroke-width="1.4"/></svg>
					<span><?php esc_html_e( 'Add all 5 scents', 'bunnybabe' ); ?></span><?php echo $icon_arrow; // phpcs:ignore ?>
				</button>
			</div>
		</div>
	</div>
</section>

<!-- Scents -->
<section class="bb-wrap bb-section bb-stack" id="scents">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( 'The collection', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Pick your chaos', 'bunnybabe' ); ?></h2>
		</div>
		<p class="bb-lead" style="max-width:380px;font-size:16px"><?php esc_html_e( 'Every scent is a 30ml Eau de Parfum at RM39. Mix any of them into a bundle and save more.', 'bunnybabe' ); ?></p>
	</div>
	<div class="bb-grid-cards">
		<?php
		foreach ( $bb['scents'] as $key => $s ) :
			$pid = bb_scent_product_id( $key );
			$q   = $pid && isset( $qtys[ $pid ] ) ? $qtys[ $pid ] : 0;
			?>
			<article class="bb-card bb-lift bb-reveal" id="<?php echo esc_attr( $key ); ?>" data-bb-card data-pid="<?php echo esc_attr( $pid ); ?>">
				<button type="button" class="bb-card__img" data-bb-open="<?php echo esc_attr( $key ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s scent */ __( 'View %s', 'bunnybabe' ), $s['name'] ) ); ?>"><img src="<?php echo esc_url( bb_asset( 'img/' . $s['poster'] ) ); ?>" alt="<?php echo esc_attr( sprintf( /* translators: %s scent */ __( 'Bunny Babe %s with its scent notes', 'bunnybabe' ), $s['name'] ) ); ?>" width="800" height="1192" loading="lazy"></button>
				<div class="bb-card__body">
					<div>
						<h3 class="bb-card__name"><?php echo esc_html( $s['name'] ); ?></h3>
						<span class="bb-card__mood"><?php echo esc_html( $s['mood'] ); ?></span>
					</div>
					<div class="bb-chips">
						<?php foreach ( $s['notes'] as $note ) : ?>
							<span class="bb-chip"><?php echo esc_html( $note ); ?></span>
						<?php endforeach; ?>
					</div>
					<div class="bb-card__buy">
						<span class="bb-price">RM39</span>
						<button type="button" class="bb-btn bb-btn--pink bb-cta bb-addbtn" data-bb-open="<?php echo esc_attr( $key ); ?>" <?php disabled( ! $pid ); ?> <?php echo $pid ? '' : 'title="' . esc_attr__( 'Run Appearance → Bunny Babe Setup first', 'bunnybabe' ) . '"'; ?>>
							<?php echo $icon_bag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?>
							<span data-bb="lbl"><?php echo $q ? esc_html( sprintf( /* translators: %d qty */ __( '%d in bag · add more', 'bunnybabe' ), $q ) ) : esc_html__( 'Add to bag', 'bunnybabe' ); ?></span>
						</button>
					</div>
				</div>
			</article>
		<?php endforeach; ?>
	</div>
</section>

<!-- Mood -->
<section class="bb-wrap bb-section bb-stack">
	<h2 class="bb-h2 bb-reveal" style="font-size:clamp(40px,5vw,64px)"><?php esc_html_e( 'Same girl. Different mood.', 'bunnybabe' ); ?></h2>
	<div class="bb-tiles">
		<a class="bb-tile bb-reveal" href="#vanilla"><small><?php esc_html_e( 'FEELING SOFT & COZY', 'bunnybabe' ); ?></small><strong>Vanilla or Marsh <span class="bb-arrow">→</span></strong></a>
		<a class="bb-tile bb-reveal" href="#secret"><small><?php esc_html_e( 'FEELING BOLD', 'bunnybabe' ); ?></small><strong>Secret <span class="bb-arrow">→</span></strong></a>
		<a class="bb-tile bb-reveal" href="#aurelle"><small><?php esc_html_e( 'FEELING ROMANTIC', 'bunnybabe' ); ?></small><strong>Aurelle <span class="bb-arrow">→</span></strong></a>
		<a class="bb-tile bb-reveal" href="#swift"><small><?php esc_html_e( 'FEELING FRESH', 'bunnybabe' ); ?></small><strong>Swift <span class="bb-arrow">→</span></strong></a>
	</div>
</section>

<!-- Videos -->
<section class="bb-wrap bb-section bb-stack" id="videos">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( 'See it in action', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Watch the chaos', 'bunnybabe' ); ?></h2>
		</div>
		<span class="bb-sticker" style="transform:rotate(2deg)"><?php esc_html_e( 'swipe for more →', 'bunnybabe' ); ?></span>
	</div>
	<div class="bb-videos">
		<?php
		$videos = array(
			array( 'ad-1', __( 'Bunny Babe perfume ad', 'bunnybabe' ) ),
			array( 'ad-2', __( 'Bunny Babe before and after perfume ad', 'bunnybabe' ) ),
			array( 'ad-3', __( 'Bunny Babe problem and solution perfume ad', 'bunnybabe' ) ),
		);
		foreach ( $videos as $v ) :
			?>
			<div class="bb-video bb-lift">
				<video src="<?php echo esc_url( bb_asset( 'video/' . $v[0] . '.mp4' ) ); ?>" poster="<?php echo esc_url( bb_asset( 'video/' . $v[0] . '.jpg' ) ); ?>" controls playsinline loop preload="metadata" aria-label="<?php echo esc_attr( $v[1] ); ?>"></video>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php $bb_reviews = function_exists( 'bb_testimonials' ) ? bb_testimonials() : array(); ?>
<?php if ( $bb_reviews ) : ?>
<!-- Testimonials -->
<section class="bb-wrap bb-section bb-stack" id="reviews">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( 'Real babes, real reviews', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'They said it, not us ♡', 'bunnybabe' ); ?></h2>
		</div>
		<div class="bb-revnav">
			<button type="button" class="bb-iconbtn" data-bb-rev="-1" aria-label="<?php esc_attr_e( 'Previous review', 'bunnybabe' ); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
			<button type="button" class="bb-iconbtn" data-bb-rev="1" aria-label="<?php esc_attr_e( 'Next review', 'bunnybabe' ); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
		</div>
	</div>
	<div class="bb-reviews" data-bb="reviews" tabindex="0" aria-label="<?php esc_attr_e( 'Customer reviews', 'bunnybabe' ); ?>">
		<?php foreach ( $bb_reviews as $i => $r ) : ?>
			<figure class="bb-review<?php echo $r['quote'] ? '' : ' bb-review--img'; ?>">
				<?php if ( $r['img'] ) : ?>
					<img src="<?php echo esc_url( $r['img'] ); ?>" alt="<?php echo esc_attr( $r['quote'] ? $r['name'] : sprintf( /* translators: %d number */ __( 'Bunny Babe customer review %d', 'bunnybabe' ), $i + 1 ) ); ?>" loading="lazy">
				<?php endif; ?>
				<?php if ( $r['quote'] ) : ?>
					<figcaption>
						<span class="bb-review__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d stars */ __( '%d out of 5 stars', 'bunnybabe' ), $r['stars'] ) ); ?>"><?php echo esc_html( str_repeat( '★', $r['stars'] ) ); ?></span>
						<blockquote>“<?php echo esc_html( $r['quote'] ); ?>”</blockquote>
						<span class="bb-review__who"><strong><?php echo esc_html( $r['name'] ); ?></strong><?php if ( $r['scent'] ) : ?><span class="bb-chip"><?php echo esc_html( $r['scent'] ); ?></span><?php endif; ?></span>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- Voucher -->
<section class="bb-wrap bb-section" id="voucher">
	<div class="bb-voucher">
		<div class="bb-voucher__copy">
			<span class="bb-sticker"><?php esc_html_e( 'first order gift ♡', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Claim your RM5 voucher', 'bunnybabe' ); ?></h2>
			<p class="bb-lead" style="color:#111"><?php esc_html_e( 'Drop your WhatsApp number and get RM5 off your first order of 2+ bottles, plus another RM5 off your second.', 'bunnybabe' ); ?></p>

			<form class="bb-voucher__form" data-bb="claim-form" novalidate>
				<label class="bb-label" for="bb-claim-phone"><?php esc_html_e( 'WhatsApp number', 'bunnybabe' ); ?></label>
				<div class="bb-voucher__row">
					<input class="bb-input" id="bb-claim-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" placeholder="012-345 6789" required>
					<input type="text" name="website" tabindex="-1" autocomplete="off" hidden>
					<button class="bb-btn bb-btn--black" type="submit"><?php esc_html_e( 'Claim voucher', 'bunnybabe' ); ?></button>
				</div>
				<span class="bb-msg" data-bb="claim-error" hidden></span>
				<span class="bb-small"><?php esc_html_e( 'One per customer.', 'bunnybabe' ); ?></span>
			</form>

			<div class="bb-voucher__form" data-bb="claim-done" hidden>
				<strong style="font-size:18px"><?php esc_html_e( "Yay, it's yours! It's added to your bag automatically once you have 2+ bottles:", 'bunnybabe' ); ?></strong>
				<div class="bb-voucher__row" style="align-items:center">
					<span class="bb-code"><?php echo esc_html( strtoupper( $bb['first_coupon'] ) ); ?></span>
					<button class="bb-btn bb-btn--black" type="button" data-bb="copy" data-code="<?php echo esc_attr( strtoupper( $bb['first_coupon'] ) ); ?>"><?php esc_html_e( 'Copy code', 'bunnybabe' ); ?></button>
				</div>
				<span class="bb-small" style="font-size:14px"><?php esc_html_e( 'Your second-order RM5 voucher comes by WhatsApp after your first order.', 'bunnybabe' ); ?></span>
				<a href="#scents" style="font-size:15px;font-weight:700"><?php esc_html_e( 'Shop now and use it →', 'bunnybabe' ); ?></a>
			</div>
		</div>
		<div class="bb-ticket-wrap">
			<div class="bb-ticket">
				<div class="bb-ticket__top">
					<small><?php esc_html_e( 'BUNNY BABE VOUCHER', 'bunnybabe' ); ?></small>
					<span class="bb-ticket__big">RM5</span>
					<span class="bb-ticket__off">OFF</span>
				</div>
				<div class="bb-ticket__cut"></div>
				<div class="bb-ticket__bottom">
					<svg width="22" height="22" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.6-9.2A5 5 0 0 1 12 6.4a5 5 0 0 1 9.6 5.4C19.5 16.4 12 21 12 21z" fill="#FF5CB8" stroke="#111" stroke-width="1.4"/></svg>
					<span><?php esc_html_e( '1st & 2nd order', 'bunnybabe' ); ?></span>
				</div>
			</div>
		</div>
	</div>
</section>

<?php if ( $has_woo ) : ?>
<?php
$bb_close = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
$bb_first = array_key_first( $bb['scents'] );
?>
<!-- Product sheet (TikTok-style variant picker) -->
<div class="bb-sheet" id="bb-product" hidden>
	<div class="bb-sheet__backdrop" data-bb-close></div>
	<div class="bb-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="bb-ps-title">
		<button type="button" class="bb-sheet__x" data-bb-close aria-label="<?php esc_attr_e( 'Close', 'bunnybabe' ); ?>"><?php echo $bb_close; // phpcs:ignore ?></button>
		<div class="bb-sheet__scroll">
			<div class="bb-ps-head">
				<img data-bb="ps-img" src="<?php echo esc_url( bb_asset( 'img/' . $bb['scents'][ $bb_first ]['bottle'] ) ); ?>" alt="" width="104" height="104">
				<div class="bb-ps-info">
					<strong class="bb-ps-price">RM39</strong>
					<div class="bb-ps-tags"><span><?php esc_html_e( 'FREE shipping 2+', 'bunnybabe' ); ?></span><span><?php esc_html_e( 'Save up to RM96', 'bunnybabe' ); ?></span></div>
					<span class="bb-ps-name" id="bb-ps-title" data-bb="ps-name"></span>
				</div>
			</div>

			<div class="bb-ps-label"><?php echo esc_html( sprintf( /* translators: %d count */ __( 'Scent (%d)', 'bunnybabe' ), count( $bb['scents'] ) ) ); ?></div>
			<div class="bb-ps-grid">
				<?php foreach ( $bb['scents'] as $key => $s ) : ?>
					<button type="button" class="bb-ps-tile" aria-pressed="false"
						data-bb-pick="<?php echo esc_attr( $key ); ?>"
						data-pid="<?php echo esc_attr( bb_scent_product_id( $key ) ); ?>"
						data-img="<?php echo esc_url( bb_asset( 'img/' . $s['bottle'] ) ); ?>"
						data-name="<?php echo esc_attr( $s['name'] ); ?>"
						data-mood="<?php echo esc_attr( $s['mood'] ); ?>">
						<img src="<?php echo esc_url( bb_asset( 'img/' . $s['poster'] ) ); ?>" alt="" width="160" height="160" loading="lazy">
						<span><?php echo esc_html( $s['name'] ); ?></span>
						<small><?php echo esc_html( $s['mood'] ); ?></small>
					</button>
				<?php endforeach; ?>
			</div>

			<div class="bb-ps-row">
				<span class="bb-label"><?php esc_html_e( 'Quantity', 'bunnybabe' ); ?></span>
				<div class="bb-stepper">
					<button type="button" data-bb="ps-minus" aria-label="<?php esc_attr_e( 'Less', 'bunnybabe' ); ?>">−</button>
					<span data-bb="ps-qty">1</span>
					<button type="button" data-bb="ps-plus" aria-label="<?php esc_attr_e( 'More', 'bunnybabe' ); ?>">+</button>
				</div>
			</div>
			<div class="bb-ps-deal"><?php esc_html_e( 'Mix any scents: 2 for RM59 · 5 for RM99 + mystery tester. Applied automatically in your bag.', 'bunnybabe' ); ?></div>
		</div>
		<div class="bb-sheet__foot">
			<button type="button" class="bb-btn bb-btn--white" data-bb="ps-add" data-bb-focus>
				<svg class="bb-wiggle" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
				<span><?php esc_html_e( 'Add to bag', 'bunnybabe' ); ?></span>
			</button>
			<button type="button" class="bb-btn bb-btn--pink bb-cta" data-bb="ps-buy"><?php esc_html_e( 'Buy now', 'bunnybabe' ); ?></button>
		</div>
	</div>
</div>

<!-- Bag sheet (Shopping cart) -->
<div class="bb-sheet bb-sheet--tall" id="bb-bag" hidden>
	<div class="bb-sheet__backdrop" data-bb-close></div>
	<div class="bb-sheet__panel" role="dialog" aria-modal="true" aria-labelledby="bb-bag-title">
		<div class="bb-sheet__head">
			<strong id="bb-bag-title"><?php esc_html_e( 'Your bag', 'bunnybabe' ); ?> (<span data-bb="count"><?php echo (int) WC()->cart->get_cart_contents_count(); ?></span>)</strong>
			<button type="button" class="bb-sheet__x bb-sheet__x--inline" data-bb-close aria-label="<?php esc_attr_e( 'Close', 'bunnybabe' ); ?>"><?php echo $bb_close; // phpcs:ignore ?></button>
		</div>
		<div class="bb-sheet__scroll">
			<div class="bb-freebar<?php echo ( $n >= (int) $bb['free_ship_min'] ) ? ' is-free' : ''; ?>" data-bb="freebar" <?php echo $is_empty ? 'hidden' : ''; ?>>
				<?php echo ( $n >= (int) $bb['free_ship_min'] ) ? esc_html__( "✓ You've got FREE shipping!", 'bunnybabe' ) : esc_html__( '🚚 Add 1 more: 2 for RM59 + FREE shipping (just RM15 more)', 'bunnybabe' ); ?>
			</div>
			<div class="bb-notices" data-bb="notices" aria-live="polite"></div>

			<div class="bb-bagempty" data-bb="bag-empty" <?php echo $is_empty ? '' : 'hidden'; ?>>
				<strong><?php esc_html_e( 'Your bag is empty, babe', 'bunnybabe' ); ?></strong>
				<span><?php esc_html_e( 'Pick a scent. Bundle prices apply automatically.', 'bunnybabe' ); ?></span>
				<button type="button" class="bb-btn bb-btn--pink bb-cta" data-bb-open="<?php echo esc_attr( $bb_first ); ?>"><?php esc_html_e( 'Pick your scents', 'bunnybabe' ); ?></button>
			</div>

			<div data-bb="bag-full" class="bb-bagfull" <?php echo $is_empty ? 'hidden' : ''; ?>>
				<div class="bb-bag__items" data-bb="items"><?php bb_render_bag_items(); ?></div>
				<div class="bb-tier">
					<strong data-bb="tier"><?php echo esc_html( bb_tier_message( $n ) ); ?></strong>
					<div class="bb-bar"><span data-bb="tier-bar" style="width:<?php echo (int) bb_tier_percent( $n ); ?>%"></span></div>
					<div style="display:flex;flex-wrap:wrap;gap:8px">
						<button type="button" class="bb-btn bb-btn--white bb-btn--sm" data-bb-open="<?php echo esc_attr( $bb_first ); ?>"><?php esc_html_e( '+ Add another scent', 'bunnybabe' ); ?></button>
						<button type="button" class="bb-btn bb-btn--black bb-btn--sm" data-bb-addall><?php esc_html_e( 'Add all 5', 'bunnybabe' ); ?></button>
					</div>
				</div>
				<div class="bb-coupon">
					<label class="bb-label" for="bb-coupon"><?php esc_html_e( 'Voucher code', 'bunnybabe' ); ?></label>
					<div class="bb-coupon__row">
						<input class="bb-input" id="bb-coupon" type="text" placeholder="BUNNY5" autocomplete="off">
						<button type="button" class="bb-btn bb-btn--white" data-bb="apply-coupon"><?php esc_html_e( 'Apply', 'bunnybabe' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="bb-sheet__foot bb-sheet__foot--total">
			<div class="bb-foottotal">
				<span><?php esc_html_e( 'Total', 'bunnybabe' ); ?> <strong data-bb="total"><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong></span>
				<small data-bb="saving-line" hidden></small>
			</div>
			<button type="button" class="bb-btn bb-btn--pink bb-cta" data-bb-checkout><?php esc_html_e( 'Checkout', 'bunnybabe' ); ?> (<span data-bb="count"><?php echo (int) WC()->cart->get_cart_contents_count(); ?></span>)</button>
		</div>
	</div>
</div>

<!-- Checkout (Order summary, full screen) -->
<section class="bb-co" id="checkout" hidden aria-labelledby="bb-co-title">
	<div class="bb-co__top">
		<button type="button" class="bb-iconbtn" data-bb-backbag aria-label="<?php esc_attr_e( 'Back to bag', 'bunnybabe' ); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
		<strong id="bb-co-title"><?php esc_html_e( 'Order summary', 'bunnybabe' ); ?></strong>
		<span class="bb-secure"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><?php esc_html_e( 'Secure', 'bunnybabe' ); ?></span>
	</div>
	<div class="bb-co__body">
		<div class="bb-co__inner">
			<div class="bb-co-saving" data-bb="saving-banner" hidden></div>
				<button type="button" class="bb-freebar" data-bb="co-upsell" data-bb-backbag style="display:block;width:100%;text-align:left;cursor:pointer;font:inherit;margin:0 0 14px" <?php echo ( 1 === (int) $n ) ? '' : 'hidden'; ?>><?php esc_html_e( '🚚 Add 1 more scent: 2 for RM59 + FREE shipping (just RM15 more) ›', 'bunnybabe' ); ?></button>
			<div class="bb-co-form"><?php echo do_shortcode( '[woocommerce_checkout]' ); ?></div>
		</div>
	</div>
	<div class="bb-co__foot">
		<div class="bb-foottotal">
			<span><?php esc_html_e( 'Total', 'bunnybabe' ); ?> <strong data-bb="total"><?php echo wp_kses_post( WC()->cart->get_total() ); ?></strong></span>
			<small data-bb="saving-line" hidden></small>
		</div>
		<button type="button" class="bb-btn bb-btn--pink bb-cta bb-co__place" data-bb="place"><?php esc_html_e( 'Place order', 'bunnybabe' ); ?></button>
	</div>
</section>
<?php endif; ?>

<!-- Affiliate -->
<section class="bb-wrap bb-section" id="affiliate">
	<div class="bb-aff">
		<div class="bb-aff__copy">
			<span class="bb-aff__marker"><?php esc_html_e( 'jom join the chaos', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2" style="font-size:clamp(44px,5.5vw,80px)"><?php esc_html_e( 'Become a Bunny Babe affiliate', 'bunnybabe' ); ?></h2>
			<p><?php esc_html_e( 'Share Bunny Babe with your followers and earn 10% commission on every sale you bring in. No stock, no risk. Sign up with a quick form.', 'bunnybabe' ); ?></p>
		</div>
		<div class="bb-aff__side">
			<div class="bb-badge"><strong>10%</strong><span>COMMISSION</span></div>
			<a class="bb-btn bb-btn--pink" href="<?php echo esc_url( $aff_url ? $aff_url : bb_whatsapp_url() ); ?>" target="_blank" rel="noopener">
				<span><?php esc_html_e( 'Join as affiliate', 'bunnybabe' ); ?></span><?php echo $icon_arrow; // phpcs:ignore ?>
			</a>
		</div>
	</div>
</section>

<?php
get_footer();
