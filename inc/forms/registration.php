<?php
/**
 * Lead post type and ACF form settings.
 *
 * @package Theme_name
 */

add_action( 'init', 'adem_register_mail_post_type' );
/**
 * Register custom post type - mail.
 */
function adem_register_mail_post_type() {
	register_post_type(
		'mail',
		array(
			'label'              => null,
			'labels'             => array(
				'name'               => 'Заявки',
				'singular_name'      => 'Заявка',
				'menu_name'          => 'Заявки',
				'all_items'          => 'Все заявки',
				'add_new'            => 'Добавить заявку',
				'add_new_item'       => 'Добавить заявку',
				'edit_item'          => 'Редактировать заявку',
				'new_item'           => 'Новая заявка',
				'view_item'          => 'Смотреть заявку',
				'search_items'       => 'Найти заявку',
				'not_found'          => 'Не найдено',
				'not_found_in_trash' => 'Не найдено в корзине',
			),
			'description'        => '',
			'public'             => true,
			'map_meta_cap'       => true,
			'rewrite'            => false,
			'menu_position'      => 23,
			'menu_icon'          => 'dashicons-email-alt',
			'supports'           => false,
			'publicly_queryable' => false,
		)
	);
}

add_action( 'acf/init', 'adem_acf_register_fields' );
/**
 * Register ACF options page and fields.
 */
function adem_acf_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page(
			array(
				'page_title'      => 'Формы',
				'menu_title'      => 'Формы',
				'menu_slug'       => 'forms',
				'post_id'         => 'forms',
				'position'        => 64,
				'update_button'   => 'Обновить',
				'updated_message' => 'Настройки обновлены',
			)
		);
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_5c18f89ca825f',
			'title'    => 'Настройки форм',
			'fields'   => array(
				array(
					'key'          => 'field_5c18f8a29941c',
					'label'        => 'Адреса для отправки почты',
					'name'         => 'emails',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Добавить почту',
					'sub_fields'   => array(
						array(
							'key'   => 'field_5c18f8ba9941d',
							'label' => 'Email',
							'name'  => 'emails_item',
							'type'  => 'email',
						),
						array(
							'key'   => 'field_dfg45hhdg34rasdgf',
							'label' => 'Нужен ли трафик в письме?',
							'name'  => 'emails_traffic',
							'type'  => 'true_false',
							'ui'    => 1,
						),
					),
				),
				array(
					'key'   => 'field_dfs2825hdsdf8234',
					'label' => 'Почта отправителя',
					'name'  => 'sender-mail',
					'type'  => 'email',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'forms',
					),
				),
			),
		)
	);
}

add_action( 'add_meta_boxes', 'adem_mail_meta_box', 1 );
/**
 * Register mail meta box.
 */
function adem_mail_meta_box() {
	add_meta_box( 'mail_meta_box', 'Заявка', 'adem_mail_meta_box_content', 'mail', 'normal', 'high' );
}

/**
 * Callback for display content in mail meta box.
 *
 * @param object $post Post object.
 */
function adem_mail_meta_box_content( $post ) {
	$meta_body = get_post_meta( $post->ID, 'mail-body', true );

	echo wp_kses_post( $meta_body );
}
