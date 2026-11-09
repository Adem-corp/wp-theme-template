<?php
/**
 * Classic presentation filters.
 *
 * @package Theme_name
 */

remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
add_filter( 'use_block_editor_for_post_type', '__return_false' );
add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
add_filter( 'use_widgets_block_editor', '__return_false' );
add_filter( 'big_image_size_threshold', '__return_false' );
add_filter( 'get_the_archive_title_prefix', '__return_empty_string' );
add_filter( 'excerpt_more', 'adem_change_excerpt_more' );

/** Remove the excerpt ellipsis. */
function adem_change_excerpt_more() {
	return '';
}
