<?php
/**
 * The template for displaying blocks section.
 *
 * @package Theme_name
 * @since 1.0.0
 */

$acf_post_id = $args['id'] ?? false;

if ( function_exists( 'have_rows' ) && have_rows( 'blocks', $acf_post_id ) ) {
	while ( have_rows( 'blocks', $acf_post_id ) ) {
		the_row();

		$layout = sanitize_file_name( get_row_layout() );
		if ( ! $layout || ! file_exists( get_template_directory() . '/layouts/blocks/' . $layout . '/template.php' ) ) {
			continue;
		}

		get_template_part(
			'layouts/blocks/' . $layout . '/template',
			null,
			array(
				'block_id' => wp_unique_id( 'adem-' . $layout . '-' ),
			)
		);
	}
}
