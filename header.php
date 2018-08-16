<?php
/**
 * The template for displaying the header
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php if ( has_nav_menu( 'head-menu' ) ) : ?>
<nav>
    <?php
    wp_nav_menu( array(
        'theme_location' => 'head-menu',
        'menu_class'     => 'head-menu',
        'depth'          => 1,
        'link_before'    => '<span class="screen-reader-text">',
        'link_after'     => '</span>',
    ) );
    ?>
</nav>
<?php endif; ?>