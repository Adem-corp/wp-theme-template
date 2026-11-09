<?php
/**
 * SVG admin upload.
 *
 * @package Theme_name
 * @since 1.0.0
 */

add_filter( 'upload_mimes', 'adem_svg_upload_allow' );
/**
 * Allows uploading svg images.
 *
 * @param array $mimes Mime types keyed by the file extension regex corresponding to those types.
 */
function adem_svg_upload_allow( $mimes ) {
	$mimes['svg'] = 'image/svg+xml';

	return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'adem_fix_svg_mime_type', 10, 5 );
/**
 * Fix svg mime type.
 *
 * @param array  $data {
 *      Values for the extension, mime type, and corrected filename.
 *
 * @type string|false $ext File extension, or false if the file doesn't match a mime type.
 * @type string|false $type File mime type, or false if the file doesn't match a mime type.
 * @type string|false $proper_filename File name with its correct extension, or false if it cannot be determined.
 * }
 *
 * @param string $file Full path to the file.
 * @param string $filename The name of the file (may differ from $file due to $file being in a tmp directory).
 * @param array  $mimes Array of mime types keyed by their file extension regex, or null if none were provided.
 * @param string $real_mime The actual mime type or false if the type cannot be determined.
 */
function adem_fix_svg_mime_type( $data, $file, $filename, $mimes, $real_mime = '' ) {
	if ( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ) {
		$dosvg = in_array( $real_mime, array( 'image/svg', 'image/svg+xml' ), true );
	} else {
		$dosvg = ( '.svg' === strtolower( substr( $filename, - 4 ) ) );
	}

	if ( $dosvg ) {
		if ( current_user_can( 'manage_options' ) ) {
			$data['ext']  = 'svg';
			$data['type'] = 'image/svg+xml';
		} else {
			$data['ext'] = false;
		}
	}

	return $data;
}

add_filter( 'wp_prepare_attachment_for_js', 'adem_show_svg_in_media_library' );
/**
 * Show svg in media library.
 *
 * @param array $response Array of prepared attachment data.
 */
function adem_show_svg_in_media_library( $response ) {
	if ( 'image/svg+xml' === $response['mime'] ) {
		$response['sizes'] = array(
			'medium' => array(
				'url' => $response['url'],
			),
			'full'   => array(
				'url' => $response['url'],
			),
		);
	}

	return $response;
}
