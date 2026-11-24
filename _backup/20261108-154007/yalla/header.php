<?php
/**
 * Site header.
 *
 * @package Yalla
 */

?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<link rel="icon" href="<?php echo esc_url( yd_asset( 'img/favicon.png' ) ); ?>" sizes="any" />
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#main"><?php echo esc_html( yd_t( 'skip' ) ); ?></a>

<header class="site-header" id="site-header">
	<div class="container site-header__inner">
		<?php yd_brand(); ?>

		<nav class="nav-wrap" aria-label="<?php esc_attr_e( 'Hauptnavigation', 'yalla' ); ?>">
			<ul class="nav">
				<?php foreach ( yd_nav_items() as $item ) : ?>
					<li class="menu-item<?php echo yd_current_group() === $item['group'] ? ' is-active' : ''; ?>">
						<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<div class="header-actions">
			<?php yd_language_switcher(); ?>
			<a class="btn btn--sm" href="<?php echo esc_url( yd_link( 'events' ) ); ?>"><?php echo esc_html( yd_t( 'hero_cta1' ) ); ?></a>
			<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php echo esc_attr( yd_t( 'menu' ) ); ?>">
				<span></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-nav" id="mobile-nav">
	<?php yd_language_switcher(); ?>
	<ul>
		<?php foreach ( yd_nav_items() as $item ) : ?>
			<li><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
		<?php endforeach; ?>
	</ul>

	<div class="mt-4">
		<p class="footer-col-title"><?php echo esc_html( yd_t( 'footer_contact' ) ); ?></p>
		<p class="small">
			<a href="mailto:<?php echo esc_attr( yd_contact( 'email' ) ); ?>"><?php echo esc_html( yd_contact( 'email' ) ); ?></a><br />
			<?php echo esc_html( yd_contact( 'location' ) ); ?>
		</p>
	</div>
</div>

<main id="main">
