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
		<p class="bb-hero__lead bb-up" style="animation-delay:.2s"><?php esc_html_e( 'Perfume EDP yang wangi macam mahal, harga student-friendly.', 'bunnybabe' ); ?> <strong style="color:#111"><?php esc_html_e( 'Ambil 2 botol, RM29.50 seketul + free pos.', 'bunnybabe' ); ?></strong></p>
		<div class="bb-hero__ctas bb-up" style="animation-delay:.3s">
			<a class="bb-btn bb-btn--black" href="#bundles"><?php esc_html_e( 'Pilih pakej · dari RM39', 'bunnybabe' ); ?> <?php echo $icon_arrow; // phpcs:ignore ?></a>
			<a class="bb-btn bb-btn--pink" href="#reviews"><?php esc_html_e( 'Tengok review dulu', 'bunnybabe' ); ?></a>
		</div>
		<div class="bb-hero__proof bb-up" style="animation-delay:.35s">
			<span class="bb-stars" aria-label="<?php esc_attr_e( '5 bintang', 'bunnybabe' ); ?>">★★★★★</span>
			<span>“Wangi...best ...terbaik...” <small><?php esc_html_e( '· pembeli Vanilla Babe', 'bunnybabe' ); ?></small></span>
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
		<div class="bb-hero__price bb-wobble"><span><?php esc_html_e( '2 BOTOL', 'bunnybabe' ); ?></span><strong>RM59</strong><span><?php esc_html_e( 'FREE POS', 'bunnybabe' ); ?></span></div>
		<div class="bb-hero__tag bb-sway"><?php esc_html_e( 'Smell cute. Do chaos.', 'bunnybabe' ); ?></div>
	</div>
</section>

<!-- Marquee -->
<div class="bb-marquee" aria-hidden="true">
	<div class="bb-marquee__track">
		<?php for ( $g = 0; $g < 2; $g++ ) : ?>
			<div class="bb-marquee__group">
				<?php
				$bb_trust = array( 'Free pos 2+ botol', 'Eau de Parfum 30ml', 'Bayar selamat dengan CHIP', 'Tracking terus ke WhatsApp', 'RM5 off dengan BUNNY5' );
				for ( $r = 0; $r < 2; $r++ ) {
					foreach ( $bb_trust as $t ) {
						echo '<span>' . esc_html( $t ) . '</span><i>♡</i>';
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
			<span class="bb-eyebrow"><?php esc_html_e( 'Pilih pakej', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'One for you. Two? Even better.', 'bunnybabe' ); ?></h2>
			<p class="bb-lead" style="color:#111"><?php esc_html_e( 'Mix & match scent ikut mood. Harga pakej auto masuk dalam bag. Makin banyak botol, makin murah seketul.', 'bunnybabe' ); ?></p>
			<div class="bb-vchip"><strong><?php echo esc_html( strtoupper( $bb['first_coupon'] ) ); ?></strong><span><?php esc_html_e( 'RM5 off first order 2 botol ke atas', 'bunnybabe' ); ?></span></div>
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
				<ul><li><?php esc_html_e( 'RM19.80 per bottle', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Save RM96', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'FREE shipping', 'bunnybabe' ); ?></li><li><?php esc_html_e( 'Collect all 5 scents', 'bunnybabe' ); ?></li></ul>
				<div class="bb-freegift">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="8" width="18" height="4"/><path d="M5 12v9h14v-9M12 8v13M12 8S10 3 7.5 4.5 9 8 12 8zM12 8s2-5 4.5-3.5S15 8 12 8z"/></svg>
					<b><?php esc_html_e( 'PERCUMA PERFUME 5ML', 'bunnybabe' ); ?></b>
					<em><?php esc_html_e( 'NILAI RM15', 'bunnybabe' ); ?></em>
				</div>
				<button type="button" class="bb-btn bb-btn--pink bb-cta bb-cta--light" data-bb-addall>
					<svg class="bb-spin" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.2 7.8L22 12l-7.8 2.2L12 22l-2.2-7.8L2 12l7.8-2.2z" fill="#fff" stroke="#111" stroke-width="1.4"/></svg>
					<span><?php esc_html_e( 'Add all 5 scents', 'bunnybabe' ); ?></span><?php echo $icon_arrow; // phpcs:ignore ?>
				</button>
			</div>
		</div>
		<div class="bb-trustrow">
			<span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg><?php esc_html_e( 'Bayar selamat (FPX, kad, e-wallet)', 'bunnybabe' ); ?></span>
			<span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3z"/><path d="M14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg><?php esc_html_e( 'Hantar dengan EasyParcel', 'bunnybabe' ); ?></span>
			<span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg><?php esc_html_e( 'Ada soalan?', 'bunnybabe' ); ?> <a href="<?php echo esc_url( bb_whatsapp_url() ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp kami', 'bunnybabe' ); ?></a></span>
		</div>
	</div>
</section>

<!-- Artis videos -->
<section class="bb-wrap bb-section bb-stack" id="videos">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( '“Perfume ni nampak mahal”', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Artis dah cuba', 'bunnybabe' ); ?></h2>
		</div>
		<span class="bb-sticker" style="transform:rotate(2deg)"><?php esc_html_e( 'tekan play →', 'bunnybabe' ); ?></span>
	</div>
	<div class="bb-videos">
		<?php
		$videos = array(
			array( 'artis-1', __( 'Artis review Bunny Babe: perfume ni nampak mahal', 'bunnybabe' ) ),
			array( 'artis-2', __( 'Artis review Bunny Babe: cari perfume wangi', 'bunnybabe' ) ),
			array( 'artis-3', __( 'Artis review Bunny Babe: thank you Bunny Babe', 'bunnybabe' ) ),
		);
		foreach ( $videos as $v ) :
			?>
			<div class="bb-video bb-lift">
				<video src="<?php echo esc_url( bb_asset( 'video/' . $v[0] . '.mp4' ) ); ?>" poster="<?php echo esc_url( bb_asset( 'video/' . $v[0] . '.jpg' ) ); ?>" controls playsinline preload="none" aria-label="<?php echo esc_attr( $v[1] ); ?>"></video>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php
// Real buyer reviews (their own words, names masked), then any added in WP admin.
$bb_reviews = array(
	array( 'name' => 'N**e', 'scent' => 'Swift', 'head' => 'My favourite smell.', 'quote' => '3rd time bought this perfume. 1st spray smell like corporate women. Then it get softer2 smell. In AC room office, Can last allday work 6-7hour. Seriously. If outside less like 4-5hour.' ),
	array( 'name' => 'ℓ**.', 'scent' => 'Marsh', 'head' => 'sumpah sedappp', 'quote' => 'bau marshmallow ni sumpah sedappp bau dia manis manis camtu tahan lama sampai berapa jam tak pasti lagi baru nak try' ),
	array( 'name' => 'a**t', 'scent' => 'Vanilla', 'head' => 'Bau tahan lamaaaaa', 'quote' => 'Bau tahan lamaaaaa walaupun 2-3 spray je. Vanilla ni bau dia ada vibes ariana grande cloud sikit but not that same. Vanilla lagi sedap di hidung saya.' ),
	array( 'name' => 'M**e', 'scent' => 'Vanilla', 'head' => 'Wangi...best ...terbaik...', 'quote' => 'Wangi...best ...terbaik... suami pn twgur spe y wangi ni.. pdahal bini dia kat sebelah...' ),
	array( 'name' => 'P**9', 'scent' => 'Aurelle', 'head' => 'Very good', 'quote' => 'The scent was very nice and last for few hours. Thanks for the gift.' ),
);
foreach ( $bb_reviews as $k => $r ) {
	$bb_reviews[ $k ] += array( 'img' => '', 'stars' => 5 );
}
if ( function_exists( 'bb_testimonials' ) ) {
	foreach ( bb_testimonials() as $r ) {
		$bb_reviews[] = $r + array( 'head' => '' );
	}
}
?>
<!-- Testimonials -->
<section class="bb-wrap bb-section bb-stack" id="reviews">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( 'Real babes, real reviews', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( "Don't just take our word ♡", 'bunnybabe' ); ?></h2>
		</div>
		<div class="bb-revnav">
			<button type="button" class="bb-iconbtn" data-bb-rev="-1" aria-label="<?php esc_attr_e( 'Previous review', 'bunnybabe' ); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>
			<button type="button" class="bb-iconbtn" data-bb-rev="1" aria-label="<?php esc_attr_e( 'Next review', 'bunnybabe' ); ?>"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
		</div>
	</div>
	<p class="bb-lead" style="margin-top:-8px"><?php esc_html_e( 'Review sebenar dari pembeli Bunny Babe, ikut ayat diorang sendiri. Nama disorok untuk privasi.', 'bunnybabe' ); ?></p>
	<div class="bb-reviews" data-bb="reviews" tabindex="0" aria-label="<?php esc_attr_e( 'Customer reviews', 'bunnybabe' ); ?>">
		<?php foreach ( $bb_reviews as $i => $r ) : ?>
			<figure class="bb-review<?php echo $r['quote'] ? '' : ' bb-review--img'; ?>">
				<?php if ( $r['img'] ) : ?>
					<img src="<?php echo esc_url( $r['img'] ); ?>" alt="<?php echo esc_attr( $r['quote'] ? $r['name'] : sprintf( /* translators: %d number */ __( 'Bunny Babe customer review %d', 'bunnybabe' ), $i + 1 ) ); ?>" loading="lazy">
				<?php endif; ?>
				<?php if ( $r['quote'] ) : ?>
					<figcaption>
						<span class="bb-review__stars" aria-label="<?php echo esc_attr( sprintf( /* translators: %d stars */ __( '%d out of 5 stars', 'bunnybabe' ), $r['stars'] ) ); ?>"><?php echo esc_html( str_repeat( '★', $r['stars'] ) ); ?></span>
						<?php if ( $r['head'] ) : ?><strong class="bb-review__head">“<?php echo esc_html( $r['head'] ); ?>”</strong><?php endif; ?>
						<blockquote><?php echo esc_html( $r['head'] ? $r['quote'] : '“' . $r['quote'] . '”' ); ?></blockquote>
						<span class="bb-review__who"><strong><?php echo esc_html( $r['name'] ); ?></strong><?php if ( $r['scent'] ) : ?><span class="bb-chip"><?php echo esc_html( $r['scent'] ); ?></span><?php endif; ?><small><?php esc_html_e( 'Pembeli sebenar', 'bunnybabe' ); ?></small></span>
					</figcaption>
				<?php endif; ?>
			</figure>
		<?php endforeach; ?>
	</div>
	<div><a class="bb-btn bb-btn--black" href="#bundles"><?php esc_html_e( 'Your turn, babe · Pilih pakej', 'bunnybabe' ); ?> <?php echo $icon_arrow; // phpcs:ignore ?></a></div>
</section>

<!-- Scents -->
<section class="bb-wrap bb-section bb-stack" id="scents">
	<div class="bb-sechead">
		<div>
			<span class="bb-eyebrow"><?php esc_html_e( 'Tak tahu nak pilih yang mana?', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Find your mood match', 'bunnybabe' ); ?></h2>
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
	<p class="bb-pickhelp"><strong><?php esc_html_e( 'Masih tak boleh pilih?', 'bunnybabe' ); ?></strong> <?php esc_html_e( 'Ambil Babe Pack, dapat kelima-lima +', 'bunnybabe' ); ?> <mark><?php esc_html_e( 'PERCUMA perfume 5ml (nilai RM15)', 'bunnybabe' ); ?></mark> <button type="button" class="bb-linkbtn" data-bb-addall><?php esc_html_e( 'RM99, free pos →', 'bunnybabe' ); ?></button></p>
</section>

<!-- Why Bunny Babe -->
<section class="bb-wrap bb-section">
	<div class="bb-why">
		<div>
			<span class="bb-eyebrow" style="color:var(--bb-pink)"><?php esc_html_e( 'Kenapa Bunny Babe', 'bunnybabe' ); ?></span>
			<h2 class="bb-h2 bb-reveal"><?php esc_html_e( 'Murah, tapi berbaloi', 'bunnybabe' ); ?></h2>
		</div>
		<div class="bb-why__grid">
			<div><strong><?php esc_html_e( 'Eau de Parfum', 'bunnybabe' ); ?></strong><span><?php esc_html_e( 'Concentration tinggi, bukan body mist.', 'bunnybabe' ); ?></span></div>
			<div><strong><?php esc_html_e( '8 jam tahan lama', 'bunnybabe' ); ?></strong><span><?php esc_html_e( 'Spray pagi, still smell cute petang.', 'bunnybabe' ); ?></span></div>
			<div><strong><?php esc_html_e( '5 personaliti', 'bunnybabe' ); ?></strong><span><?php esc_html_e( 'Satu scent untuk setiap mood. Collect semua.', 'bunnybabe' ); ?></span></div>
			<div><strong><?php esc_html_e( 'Free pos 2 botol', 'bunnybabe' ); ?></strong><span><?php esc_html_e( 'Tracking dihantar terus ke WhatsApp you.', 'bunnybabe' ); ?></span></div>
		</div>
		<div><a class="bb-btn bb-btn--pink" href="#bundles"><?php esc_html_e( 'Pilih pakej', 'bunnybabe' ); ?> <?php echo $icon_arrow; // phpcs:ignore ?></a></div>
	</div>
</section>

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

<!-- FAQ -->
<section class="bb-wrap bb-wrap--narrow bb-section bb-faq" id="faq">
	<h2 class="bb-h2 bb-reveal" style="text-align:center;margin-bottom:24px"><?php esc_html_e( 'Sebelum you checkout', 'bunnybabe' ); ?></h2>
	<details open><summary><?php esc_html_e( 'Boleh campur scent dalam pakej?', 'bunnybabe' ); ?></summary><p><?php esc_html_e( 'Boleh. Untuk 2 botol, pilih dua scent berbeza atau scent yang sama. Babe Pack ada satu botol setiap scent.', 'bunnybabe' ); ?></p></details>
	<details><summary><?php esc_html_e( 'Berapa kos penghantaran?', 'bunnybabe' ); ?></summary><p><?php esc_html_e( 'RM5 untuk 1 botol. Free shipping untuk 2 botol ke atas.', 'bunnybabe' ); ?></p></details>
	<details><summary><?php esc_html_e( 'Boleh bayar guna apa?', 'bunnybabe' ); ?></summary><p><?php esc_html_e( 'FPX online banking, kad debit/kredit dan e-wallet, melalui CHIP.', 'bunnybabe' ); ?></p></details>
	<details><summary><?php esc_html_e( 'Apa dalam Babe Pack?', 'bunnybabe' ); ?></summary><p><?php esc_html_e( 'Aurelle, Secret, Swift, Vanilla dan Marsh, setiap satu 30ml, plus PERCUMA satu perfume 5ml bernilai RM15 (bau surprise!).', 'bunnybabe' ); ?></p></details>
	<details><summary><?php esc_html_e( 'Macam mana nak track order?', 'bunnybabe' ); ?></summary><p><?php esc_html_e( 'Link tracking dihantar ke WhatsApp you, atau masukkan nombor order dan nombor telefon di page Track Order.', 'bunnybabe' ); ?></p></details>
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
			<div class="bb-ps-deal"><?php esc_html_e( 'Mix any scents: 2 for RM59 · 5 for RM99 + PERCUMA perfume 5ml (nilai RM15). Applied automatically in your bag.', 'bunnybabe' ); ?></div>
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
