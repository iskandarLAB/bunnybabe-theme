<?php
/**
 * Wrapper for WooCommerce pages that are not redirected (single product, account).
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="bb-page">
	<div class="bb-wrap">
		<div class="bb-content bb-woo"><?php woocommerce_content(); ?></div>
	</div>
</section>
<?php
get_footer();
