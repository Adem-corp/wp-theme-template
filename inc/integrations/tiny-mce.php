<?php
/**
 * TinyMCE settings.
 *
 * @package Theme_name
 * @since 1.0.0
 */

add_filter( 'tiny_mce_before_init', 'adem_tmce_change_toolbar' );
/**
 * Add custom style formats in toolbar.
 *
 * @param array $args An array with TinyMCE config.
 */
function adem_tmce_change_toolbar( $args ) {
	$style_formats            = array(
		array(
			'title' => 'Стиль текста',
			'items' => array(
				array(
					'title'    => 'Regular',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => array(
						'font-weight' => '400',
					),
				),
				array(
					'title'    => 'Medium',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => array(
						'font-weight' => '500',
					),
				),
				array(
					'title'    => 'Bold',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => array(
						'font-weight' => '700',
					),
				),
				array(
					'title'    => 'Extra Bold',
					'selector' => 'ul, ol, a, p, span',
					'styles'   => array(
						'font-weight' => '800',
					),
				),
			),
		),
	);
	$args['fontsize_formats'] = '6px 8px 10px 12px 14px 16px 18px 20px 22px 24px 26px 28px 30px 32px 34px 36px';
	$args['style_formats']    = wp_json_encode( $style_formats );

	return $args;
}

add_action( 'init', 'adem_register_tmce_buttons' );
/**
 * Register custom buttons.
 */
function adem_register_tmce_buttons() {
	if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	if ( 'true' !== get_user_option( 'rich_editing' ) ) {
		return;
	}

	add_filter( 'mce_external_plugins', 'adem_add_tmce_plugin' );
	/**
	 * Add external plugin to TinyMCE.
	 *
	 * @param array $external_plugins An array of external TinyMCE plugins.
	 */
	function adem_add_tmce_plugin( $external_plugins ) {
		$external_plugins['adem_buttons'] = get_template_directory_uri() . '/tmce-button.js';

		return $external_plugins;
	}

	add_filter( 'mce_buttons', 'adem_add_tmce_buttons' );
	/**
	 * Add buttons to first-row list TinyMCE.
	 *
	 * @param array $mce_buttons First-row list of buttons.
	 */
	function adem_add_tmce_buttons( $mce_buttons ) {
		array_push( $mce_buttons, 'adem_buttons' );

		return $mce_buttons;
	}
}
