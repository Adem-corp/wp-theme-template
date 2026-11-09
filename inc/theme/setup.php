<?php
/**
 * Classic theme support and menus.
 *
 * @package Theme_name
 */

add_action( 'after_setup_theme', 'adem_theme_setup' );

/** Register theme support and menus. */
function adem_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'style', 'script' ) );
	add_theme_support( 'editor-styles' );
	add_editor_style();
	register_nav_menus( array( 'menu_main' => 'Основное меню' ) );
}
