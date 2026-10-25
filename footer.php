<?php defined( 'ABSPATH' ) || exit; ?>
</main>

<footer class="bb-footer">
	<div class="bb-wrap bb-footer__inner">
		<div class="bb-footer__brand">
			<img src="<?php echo esc_url( bb_asset( 'img/logo.png' ) ); ?>" alt="<?php esc_attr_e( 'Bunny Babe', 'bunnybabe' ); ?>" width="580" height="104">
			<span><?php esc_html_e( 'Affordable fragrance for everyday girls.', 'bunnybabe' ); ?></span>
		</div>
		<div class="bb-footer__cols">
			<div>
				<strong><?php esc_html_e( 'Shop', 'bunnybabe' ); ?></strong>
				<a href="<?php echo esc_url( home_url( '/#scents' ) ); ?>"><?php esc_html_e( 'All scents', 'bunnybabe' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/#bundles' ) ); ?>"><?php esc_html_e( 'Bundles', 'bunnybabe' ); ?></a>
			</div>
			<div>
				<strong><?php esc_html_e( 'Help', 'bunnybabe' ); ?></strong>
				<a href="<?php echo esc_url( bb_track_url() ); ?>"><?php esc_html_e( 'Track order', 'bunnybabe' ); ?></a>
				<span><?php esc_html_e( 'Shipping RM5 · FREE for 2+ bottles', 'bunnybabe' ); ?></span>
				<a href="<?php echo esc_url( bb_whatsapp_url() ); ?>"><?php esc_html_e( 'Contact', 'bunnybabe' ); ?></a>
			</div>
			<div>
				<strong><?php esc_html_e( 'Policies', 'bunnybabe' ); ?></strong>
				<a href="<?php echo esc_url( home_url( '/shipping-policy/' ) ); ?>"><?php esc_html_e( 'Shipping', 'bunnybabe' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/refund-returns/' ) ); ?>"><?php esc_html_e( 'Refund & returns', 'bunnybabe' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'bunnybabe' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms', 'bunnybabe' ); ?></a>
			</div>
		</div>
	</div>
	<div class="bb-wrap bb-footer__legal">© <?php echo esc_html( gmdate( 'Y' ) ); ?> Bunny Babe. <?php esc_html_e( 'Cute outside, chaos inside.', 'bunnybabe' ); ?></div>
</footer>

<?php
if ( is_front_page() && class_exists( 'WooCommerce' ) && WC()->cart ) :
	$bb_cnt = (int) WC()->cart->get_cart_contents_count();
	?>
<!-- Sticky buy bar (TikTok-style) -->
<div class="bb-buybar">
	<a class="bb-buybar__icon" href="<?php echo esc_url( bb_whatsapp_url() ); ?>" target="_blank" rel="noopener">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a9 9 0 0 1-13.4 7.8L3 21l1.2-4.4A9 9 0 1 1 21 12z"/></svg>
		<span><?php esc_html_e( 'Chat', 'bunnybabe' ); ?></span>
	</a>
	<button type="button" class="bb-buybar__icon" data-bb-openbag data-bb="bag-target" aria-label="<?php esc_attr_e( 'Open bag', 'bunnybabe' ); ?>">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
		<span class="bb-dot" data-bb="count" <?php echo $bb_cnt ? '' : 'hidden'; ?>><?php echo (int) $bb_cnt; ?></span>
		<span><?php esc_html_e( 'Bag', 'bunnybabe' ); ?></span>
	</button>
	<button type="button" class="bb-btn bb-btn--pink bb-buybar__buy" data-bb="buy-main" data-mode="<?php echo $bb_cnt ? 'checkout' : 'buy'; ?>">
		<?php if ( $bb_cnt ) : ?>
			<strong><?php echo esc_html( sprintf( /* translators: %d count */ __( 'Checkout (%d)', 'bunnybabe' ), $bb_cnt ) ); ?></strong>
			<small><?php echo wp_kses_post( WC()->cart->get_total() ); ?></small>
		<?php else : ?>
			<strong><?php esc_html_e( 'Buy now', 'bunnybabe' ); ?></strong>
			<small><?php esc_html_e( 'RM39 · FREE shipping 2+', 'bunnybabe' ); ?></small>
		<?php endif; ?>
	</button>
</div>
<div class="bb-toast" data-bb="toast" role="status" aria-live="polite"></div>
<div class="bb-recent" data-bb="recent" aria-live="polite" hidden>
	<button type="button" class="bb-recent__main" data-bb-open="">
		<img src="" alt="" width="52" height="52">
		<span class="bb-recent__text"><strong></strong><span></span><small></small></span>
	</button>
	<button type="button" class="bb-recent__x" data-bb="recent-x" aria-label="<?php esc_attr_e( 'Hide recent purchases', 'bunnybabe' ); ?>">×</button>
</div>
<?php endif; ?>

</div>
<?php wp_footer(); ?>
</body>
</html>
