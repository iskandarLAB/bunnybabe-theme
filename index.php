<?php
/**
 * Fallback template.
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<section class="bb-page">
	<div class="bb-wrap bb-wrap--narrow">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article class="bb-content">
					<h2 class="bb-h2"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<h1 class="bb-h1"><?php esc_html_e( 'Nothing here, babe', 'bunnybabe' ); ?></h1>
			<p><a class="bb-btn bb-btn--black" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to shop', 'bunnybabe' ); ?></a></p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
