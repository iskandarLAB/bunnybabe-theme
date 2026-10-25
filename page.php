<?php
/**
 * Default page — used for WooCommerce's order-received page, policies, etc.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="bb-page">
	<div class="bb-wrap bb-wrap--narrow">
		<?php
		while ( have_posts() ) :
			the_post();
			if ( ! ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ) {
				echo '<h1 class="bb-h1">' . esc_html( get_the_title() ) . '</h1>';
			}
			echo '<div class="bb-content">';
			the_content();
			echo '</div>';
		endwhile;
		?>
	</div>
</section>
<?php
get_footer();
