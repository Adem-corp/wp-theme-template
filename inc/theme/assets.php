<?php
/**
 * Front-end assets built from this theme's own sources.
 *
 * @package Theme_name
 */

add_action( 'wp_enqueue_scripts', 'adem_enqueue_scripts' );

/** Enqueue the compiled stylesheet and script. */
function adem_enqueue_scripts() {
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'global-styles' );

	$uri = get_template_directory_uri();
	wp_enqueue_style( 'adem', get_stylesheet_uri(), array(), ADEM_THEME_VERSION );
	wp_enqueue_script( 'adem', $uri . '/assets/js/main.min.js', array(), ADEM_THEME_VERSION, true );
	wp_localize_script( 'adem', 'adem_ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
}
