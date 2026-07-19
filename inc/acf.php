<?php
/**
 * ACF and ACF Extended settings
 *
 * @package Theme_name
 * @since 1.0.0
 */

// Return acf row index from 0.
add_filter( 'acf/settings/row_index_offset', '__return_zero' );

add_filter( 'acfe/flexible/thumbnail', 'adem_layout_thumbnail_url', 10, 3 );
/**
 * Set url for blocks preview.
 *
 * @param int|string $thumbnail  Thumbnail ID/URL.
 * @param array      $field      Field settings.
 * @param array      $layout     Layout settings.
 */
function adem_layout_thumbnail_url( $thumbnail, $field, $layout ) {
	return get_template_directory_uri() . '/layouts/blocks/' . $layout['name'] . '/preview.jpg';
}

add_action( 'acf/init', 'adem_acf_register_options_pages' );
/**
 * Registers option pages.
 */
function adem_acf_register_options_pages() {
	if ( function_exists( 'acf_add_options_page' ) ) {
		 acf_add_options_page(
			array(
				'page_title'      => 'Настройки темы',
				'menu_title'      => 'Настройки темы',
				'menu_slug'       => 'theme-options',
				'capability'      => 'edit_posts',
				'position'        => 64,
				'update_button'   => 'Обновить',
				'updated_message' => 'Настройки обновлены',
			)
		);
	}
}

