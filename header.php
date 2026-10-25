<?php
defined( 'ABSPATH' ) || exit;
$bb_home  = is_front_page() ? '' : home_url( '/' );
$bb_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( trim( ( $bb_count ? 'bb-has-bag ' : '' ) . ( is_front_page() ? 'bb-front' : '' ) ) ); ?>>
<?php wp_body_open(); ?>
<a class="bb-skip" href="#bb-main"><?php esc_html_e( 'Skip to content', 'bunnybabe' ); ?></a>
<div class="bb-site">

<div class="bb-topbar"><span><?php echo esc_html( get_theme_mod( 'bb_announcement', 'FREE shipping for 2+ bottles ♡' ) ); ?></span></div>

<header class="bb-header">
	<div class="bb-wrap bb-header__inner">
		<a class="bb-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( bb_asset( 'img/logo.png' ) ); ?>" alt="<?php esc_attr_e( 'Bunny Babe', 'bunnybabe' ); ?>" width="580" height="104">
		</a>
		<nav class="bb-nav" aria-label="<?php esc_attr_e( 'Main', 'bunnybabe' ); ?>">
			<a href="<?php echo esc_url( $bb_home . '#videos' ); ?>"><?php esc_html_e( 'Videos', 'bunnybabe' ); ?></a>
			<a href="<?php echo esc_url( $bb_home . '#scents' ); ?>"><?php esc_html_e( 'Scents', 'bunnybabe' ); ?></a>
			<a href="<?php echo esc_url( $bb_home . '#bundles' ); ?>"><?php esc_html_e( 'Bundles', 'bunnybabe' ); ?></a>
			<a href="<?php echo esc_url( $bb_home . '#affiliate' ); ?>"><?php esc_html_e( 'Affiliate', 'bunnybabe' ); ?></a>
			<a href="<?php echo esc_url( bb_track_url() ); ?>"><?php esc_html_e( 'Track order', 'bunnybabe' ); ?></a>
			<a class="bb-bagbtn" href="<?php echo esc_url( $bb_home . '#bag' ); ?>"<?php echo is_front_page() ? ' data-bb-openbag' : ''; ?>>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l-1 13H7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
				<span><?php esc_html_e( 'Bag', 'bunnybabe' ); ?> (<span data-bb="count"><?php echo (int) $bb_count; ?></span>)</span>
			</a>
		</nav>
	</div>
</header>

<main id="bb-main">
