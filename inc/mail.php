<?php
/**
 * Mail settings
 *
 * @package Theme_name
 * @since 1.0.0
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
				'new_item'           => 'Новая заявку',
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

add_action( 'add_meta_boxes', 'mail_meta_box', 1 );
/**
 * Register mail meta box.
 */
function mail_meta_box() {
	add_meta_box( 'mail_meta_box', 'Заявка', 'mail_meta_box_function', 'mail', 'normal', 'high' );
}

/**
 * Callback for display content in mail meta box.
 *
 * @param object $post Post object.
 */
function mail_meta_box_function( $post ) {
	$meta_body = get_post_meta( $post->ID, 'mail-body', true );

	echo wp_kses_post( $meta_body );
}

add_action( 'wp_ajax_send_mail', 'adem_send_mail' );
add_action( 'wp_ajax_nopriv_send_mail', 'adem_send_mail' );
/**
 * Handler for ajax mails.
 */
function adem_send_mail() {
	$nonce_field = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : null;
	$form_name   = isset( $_POST['form_name'] ) ? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : null;

	if ( ! $nonce_field && ! wp_verify_nonce( $nonce_field, $form_name ) ) {
		exit;
	}

	$time_on_page = isset( $_POST['time_on_page'] ) ? sanitize_text_field( wp_unslash( $_POST['time_on_page'] ) ) : 0;
	$typing_speed = $_POST['typing_speed'] ?? '[]'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing.

	if ( is_suspicious_submission( $time_on_page, $typing_speed ) ) {
		exit;
	}

	$mail    = isset( $_POST['name'] ) ? 'Имя: ' . sanitize_text_field( wp_unslash( $_POST['name'] ) ) . '<br/>' : '';
	$tel     = isset( $_POST['tel'] ) ? sanitize_text_field( wp_unslash( $_POST['tel'] ) ) : null;
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : null;
	$message = isset( $_POST['message'] ) ? sanitize_text_field( wp_unslash( $_POST['message'] ) ) : null;
	$order   = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : null;
	$referer = isset( $_POST['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : null;

	$mail .= isset( $tel ) ? 'Телефон: <a href="tel:' . adem_clear_tel( $tel ) . '">' . $tel . '</a><br/>' : '';
	$mail .= isset( $email ) ? 'Email: <a href="mailto:' . $email . '">' . $email . '</a><br/>' : '';
	$mail .= isset( $message ) ? 'Сообщение: ' . $message . '<br/>' : '';
	$mail .= ! empty( $order ) ? 'Заказ: ' . $order . '<br/>' : '';
	$mail .= isset( $referer ) ? 'Страница: ' . $referer . '<br/>' : '';

	if ( ! empty( $_FILES['files'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';

		$files = $_FILES['files'];

		foreach ( $files['name'] as $key => $data ) {
			$file = array(
				'name'     => $files['name'][ $key ],
				'type'     => $files['type'][ $key ],
				'tmp_name' => $files['tmp_name'][ $key ],
				'error'    => $files['error'][ $key ],
				'size'     => $files['size'][ $key ],
			);

			$attach_id = media_handle_sideload( $file );

			if ( ! is_wp_error( $attach_id ) ) {
				$mail .= 'Файл: <a href="' . wp_get_attachment_url( $attach_id ) . '" target="_blank">Ссылка на файл</a><br/>';
			}
		}
	}

	$post_data = array(
		'post_title'  => $form_name,
		'post_status' => 'pending',
		'post_author' => 1,
		'post_type'   => 'mail',
	);
	$post_ID   = wp_insert_post( $post_data );
	$traffic   = isset( $_COOKIE['traffic_source'] ) ? 'Трафик: ' . sanitize_text_field( wp_unslash( $_COOKIE['traffic_source'] ) ) . '<br/>' : '';
	$meta_body = $mail . $traffic;

	update_post_meta( $post_ID, 'mail-body', $meta_body );

	if ( have_rows( 'emails', 'forms' ) ) {
		$subject = get_bloginfo( 'name' ) . ' - ' . $form_name;
		$headers = 'Content-type: text/html; charset="utf-8"';

		while ( have_rows( 'emails', 'forms' ) ) {
			the_row();

			$mail_to   = get_sub_field( 'emails_item' );
			$mail_body = $mail;

			if ( get_sub_field( 'emails_traffic' ) ) {
				$mail_body .= $traffic;
			}

			wp_mail( $mail_to, $subject, $mail_body, $headers );
		}
	}

	wp_send_json_success();

	wp_die();
}

add_filter( 'wp_mail_from_name', 'adem_change_mail_name' );
/**
 * Changes the name of outgoing mail.
 */
function adem_change_mail_name(): string {
	return get_bloginfo( 'name' );
}

add_filter( 'wp_mail_from', 'adem_change_mail_email' );
/**
 * Changes the email of outgoing mail.
 */
function adem_change_mail_email(): string {
	return get_field( 'sender-mail', 'forms' ) ?? get_bloginfo( 'admin_email' );
}

/**
 * Detects whether a form submission is likely to be from a bot.
 *
 * The function evaluates the time a user spent on the page and their typing speed pattern
 * to determine suspicious submissions. It also logs request details for further analysis.
 *
 * Rules applied:
 * 1. Submissions with time on page < 3 seconds are flagged as bots.
 * 2. If typing speed data is empty and time on page < 5 seconds → flagged as bots.
 * 3. If typing intervals are too uniform (difference < 10 ms across > 5 keystrokes) → flagged as bots.
 *
 * @param int    $time_on_page Time spent on the page in milliseconds.
 * @param string $typing_speed_json JSON-encoded array of typing intervals in milliseconds.
 *
 * @return bool True if the submission is considered suspicious (likely bot), false otherwise.
 */
function is_suspicious_submission( $time_on_page, $typing_speed_json ) {
	$log      = array(
		'ip'           => $_SERVER['REMOTE_ADDR'] ?? null, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing.
		'time'         => current_time( 'mysql' ),
		'time_on_page' => $time_on_page,
		'typing_speed' => $typing_speed_json,
		'post'         => $_POST, //phpcs:ignore WordPress.Security.NonceVerification.Missing
	);
	$log_line = wp_json_encode( $log, JSON_UNESCAPED_UNICODE ) . PHP_EOL;

	error_log( $log_line, 3, WP_CONTENT_DIR . '/antibot.log' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

	$time_on_page = intval( $time_on_page );
	$typing_speed = json_decode( $typing_speed_json, true );

	if ( $time_on_page < 3000 ) {
		return true;
	}

	if ( empty( $typing_speed ) && $time_on_page < 5000 ) {
		return true;
	}

	if ( ! empty( $typing_speed ) && count( $typing_speed ) > 5 ) {
		$min      = min( $typing_speed );
		$max      = max( $typing_speed );
		$avg      = array_sum( $typing_speed ) / count( $typing_speed );
		$variance = 0;

		foreach ( $typing_speed as $t ) {
			$variance += pow( $t - $avg, 2 );
		}

		$std_dev  = sqrt( $variance / count( $typing_speed ) );
		$coef_var = $std_dev / ( $avg ?: 1 );

		if ( ( $max - $min ) < 16 ) {
			return true;
		}

		if ( $coef_var < 0.1 ) {
			return true;
		}

		$outliers = array_filter(
			$typing_speed,
			function ( $t ) use ( $avg ) {
				return abs( $t - $avg ) > 50;
			}
		);

		if ( count( $outliers ) <= 1 ) {
			return true;
		}

		$small_threshold = 4;
		$max_chain       = 0;
		$current_chain   = 0;

		foreach ( $typing_speed as $i => $t ) {
			if ( $t <= $small_threshold ) {
				++$current_chain;
			} else {
				$current_chain = 0;
			}

			$max_chain = max( $max_chain, $current_chain );
		}

		if ( $max_chain >= 3 ) {
			return true;
		}

		$chain                 = 1;
		$count_of_typing_speed = count( $typing_speed );

		for ( $i = 1; $i < $count_of_typing_speed; $i++ ) {
			if ( abs( $typing_speed[ $i ] - $typing_speed[ $i - 1 ] ) < 2 ) {
				++$chain;
			} else {
				$chain = 1;
			}

			if ( $chain >= 4 ) {
				return true;
			}
		}
	}

	return false;
}
