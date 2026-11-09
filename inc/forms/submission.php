<?php
/**
 * Generic AJAX lead submission.
 *
 * @package Theme_name
 */

add_action( 'wp_ajax_send_mail', 'adem_send_mail' );
add_action( 'wp_ajax_nopriv_send_mail', 'adem_send_mail' );
add_filter( 'wp_mail_from_name', 'adem_change_mail_name' );
add_filter( 'wp_mail_from', 'adem_change_mail_email' );

/** Validate, save, and deliver a lead from the send_mail AJAX action. */
function adem_send_mail() {
	$form_name = isset( $_POST['form_name'] ) && is_string( $_POST['form_name'] )
		? sanitize_text_field( wp_unslash( $_POST['form_name'] ) ) : '';
	$nonce     = isset( $_POST['nonce'] ) && is_string( $_POST['nonce'] )
		? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

	if ( '' === $form_name || '' === $nonce || ! wp_verify_nonce( $nonce, $form_name ) ) {
		wp_send_json_error( array( 'message' => 'Invalid form nonce.' ), 403 );
		return;
	}
	if ( adem_is_suspicious_submission() ) {
		wp_send_json_error( array( 'message' => 'Submission rejected.' ), 400 );
		return;
	}

	$fields = adem_get_submission_fields();
	if ( empty( $fields ) && empty( $_FILES['files'] ) ) {
		wp_send_json_error( array( 'message' => 'Empty submission.' ), 400 );
		return;
	}

	$post_id = wp_insert_post(
		array(
			'post_title'  => $form_name,
			'post_status' => 'pending',
			'post_type'   => 'mail',
		),
		true
	);
	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_send_json_error( array( 'message' => 'Could not save submission.' ), 500 );
		return;
	}

	$rows = array();
	foreach ( $fields as $label => $value ) {
		$rows[] = '<strong>' . esc_html( $label ) . ':</strong> ' . esc_html( $value );
	}
	$body = implode( '<br/>', $rows );
	if ( $body ) {
		$body .= '<br/>';
	}

	$attachments = array();
	if ( ! empty( $_FILES['files'] ) && is_array( $_FILES['files'] ) && isset( $_FILES['files']['name'] ) ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		$uploads = $_FILES['files']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- media_handle_sideload() validates each upload.
		$names   = is_array( $uploads['name'] ) ? $uploads['name'] : array( $uploads['name'] );
		foreach ( array_keys( $names ) as $key ) {
			$file = array();
			foreach ( array( 'name', 'type', 'tmp_name', 'error', 'size' ) as $part ) {
				$source        = $uploads[ $part ] ?? null;
				$file[ $part ] = is_array( $source ) ? ( $source[ $key ] ?? null ) : ( 0 === $key ? $source : null );
			}
			if ( UPLOAD_ERR_OK !== (int) $file['error'] || ! $file['name'] ) {
				continue;
			}
			$attachment_id = media_handle_sideload( $file, $post_id );
			if ( is_wp_error( $attachment_id ) ) {
				continue;
			}
			$url = wp_get_attachment_url( $attachment_id );
			if ( $url ) {
				$body .= '<strong>Файл:</strong> <a href="' . esc_url( $url ) . '">' . esc_html( $file['name'] ) . '</a><br/>';
			}
			$path = get_attached_file( $attachment_id );
			if ( $path ) {
				$attachments[] = $path;
			}
		}
	}

	$traffic = adem_get_traffic_summary();
	update_post_meta( $post_id, 'mail-body', $body . ( $traffic ? '<strong>Трафик:</strong> ' . esc_html( $traffic ) . '<br/>' : '' ) );
	if ( $traffic ) {
		update_post_meta( $post_id, 'traffic_source', $traffic );
	}

	if ( function_exists( 'have_rows' ) && have_rows( 'emails', 'forms' ) ) {
		$subject = get_bloginfo( 'name' ) . ' - ' . $form_name;
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		while ( have_rows( 'emails', 'forms' ) ) {
			the_row();
			$recipient = sanitize_email( (string) get_sub_field( 'emails_item' ) );
			if ( ! is_email( $recipient ) ) {
				continue;
			}
			$mail_body = $body;
			if ( get_sub_field( 'emails_traffic' ) && $traffic ) {
				$mail_body .= '<strong>Трафик:</strong> ' . esc_html( $traffic ) . '<br/>';
			}
			wp_mail( $recipient, $subject, $mail_body, $headers, $attachments );
		}
	}

	wp_send_json_success();
}

/** Accept scalar form fields without requiring project-specific field names. */
function adem_get_submission_fields() {
	// The only caller validates the form nonce before reading these fields.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$excluded = array( 'action', 'nonce', 'form_name', 'time_on_page', 'typing_speed', '_wp_http_referer' );
	$fields   = array();
	foreach ( $_POST as $key => $value ) {
		if ( in_array( $key, $excluded, true ) || ! is_string( $value ) ) {
			continue;
		}
		$label = sanitize_text_field( (string) $key );
		$value = sanitize_textarea_field( wp_unslash( $value ) );
		if ( '' !== $label && '' !== $value ) {
			$fields[ $label ] = $value;
		}
	}
	if ( ! empty( $_POST['_wp_http_referer'] ) && is_string( $_POST['_wp_http_referer'] ) ) {
		$fields['Страница'] = esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing
	return $fields;
}

/** Use the site name for outgoing mail. */
function adem_change_mail_name() {
	return get_bloginfo( 'name' );
}

/** Use the configured sender address, falling back to the admin address. */
function adem_change_mail_email() {
	$sender = function_exists( 'get_field' ) ? get_field( 'sender-mail', 'forms' ) : '';
	return $sender && is_email( $sender ) ? $sender : get_bloginfo( 'admin_email' );
}
