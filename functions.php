<?php
/**
 * Main functions.
 *
 * @package Theme_name
 * @since 1.0.0
 */

// TODO сделать скриншот темы.
// TODO заменить Theme_name на название темы во всех файлах темы.
// TODO в package.json указать автора и репозиторий (если есть).
// TODO обновить версии модулей в package.json.

define( 'ADEM_THEME_VERSION', '1.0.0' );

add_action( 'after_setup_theme', 'adem_theme_setup' );
/**
 * Adds feature theme support.
 */
function adem_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'style',
			'script',
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style();
}

register_nav_menus(
	array(
		'menu_main' => 'Основное меню',
	)
);

add_action( 'wp_enqueue_scripts', 'adem_enqueue_scripts' );
/**
 * Enqueue scripts and styles.
 */
function adem_enqueue_scripts() {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );
	wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/assets/vendor/css/fancybox.css', array(), '1.0.0' ); // TODO прописать версии.
	wp_enqueue_script( 'fancybox', get_template_directory_uri() . '/assets/vendor/js/fancybox.umd.js', array(), '1.0.0', true ); // TODO прописать версии.
	wp_enqueue_style( 'swiper', get_template_directory_uri() . '/assets/vendor/css/swiper-bundle.min.css', array(), '1.0.0' ); // TODO прописать версии.
	wp_enqueue_script( 'swiper', get_template_directory_uri() . '/assets/vendor/js/swiper-bundle.min.js', array(), '1.0.0', true ); // TODO прописать версии.
	wp_enqueue_style( 'adem', get_stylesheet_uri(), array(), ADEM_THEME_VERSION );
	wp_enqueue_script( 'adem', get_template_directory_uri() . '/assets/js/main.min.js', array(), ADEM_THEME_VERSION, true );
	wp_localize_script( 'adem', 'adem_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}

// Remove svg filters.
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

// Return classic widgets.
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );

// Allow big images.
add_filter( 'big_image_size_threshold', '__return_false' );

// Remove archive title prefix.
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );

add_filter( 'excerpt_more', 'adem_change_excerpt_more' );
/**
 * Remove the ellipsis at the end of the string.
 */
function adem_change_excerpt_more() {
	return '';
}

require 'inc/acf.php';
require 'inc/mail.php';
require 'inc/svg.php';
require 'inc/theme-functions.php';
require 'inc/tiny-mce.php';
require 'inc/traffic.php';
