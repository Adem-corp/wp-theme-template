<?php
/**
 * User traffic source tracking.
 *
 * Detects the acquisition source (UTM parameters or HTTP referrer) for the
 * current visitor and stores it in cookies for later use (e.g. in a lead
 * form or CRM integration). Uses a "first touch" model: once a source is
 * stored for the visitor, it is kept for the lifetime of the cookie and
 * not overwritten by subsequent direct or organic visits.
 *
 * @package Theme_name
 * @since   1.0.0
 */

add_action( 'init', 'adem_set_user_traffic_cookies' );

/**
 * Detect and store the current visitor's traffic source.
 *
 * Reads UTM query parameters first; if none are present, falls back to
 * parsing the HTTP referrer and comparing its host against the current
 * site host to distinguish direct / referral / organic (search engine)
 * traffic.
 *
 * Skips execution in admin, AJAX, cron and REST contexts. Does nothing if
 * a source has already been stored for the visitor (first-touch model),
 * and only sets cookies when a new source is actually determined, to
 * avoid unnecessary `Set-Cookie` headers on cached pages.
 *
 * Persists two cookies for {@see MONTH_IN_SECONDS}:
 * - `source_cookie`   raw JSON-encoded source data (medium, source, campaign, term, content).
 * - `traffic_source`  a human-readable summary string built from that data.
 *
 * Note: `traffic_source` cookie value is not escaped here — if it is ever
 * echoed on the front end, it must be passed through `esc_html()` first.
 *
 * Hooked on `init` so it runs on every front-end request.
 *
 * @since 1.0.0
 *
 * @return void
 */
function adem_set_user_traffic_cookies() {
	// Skip admin, AJAX, cron and REST requests entirely.
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || adem_is_rest_request() ) {
		return;
	}

	// First-touch: if a source is already stored, keep it and do nothing.
	if ( isset( $_COOKIE['source_cookie'] ) ) {
		$existing_source = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['source_cookie'] ) ), true );

		if ( is_array( $existing_source ) && ! empty( $existing_source['medium'] ) ) {
			return;
		}
	}

	$search_systems = array( 'google', 'yandex', 'mail.ru', 'rambler', 'bing' );
	$source_array   = array(
		'medium'   => null,
		'source'   => null,
		'campaign' => null,
		'term'     => null,
		'content'  => null,
	);

	// Public attribution parameters cannot carry a form nonce.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['utm_medium'] ) || isset( $_GET['utm_source'] ) ) {
		$source_array['medium']   = isset( $_GET['utm_medium'] ) && is_string( $_GET['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) ) : 'campaign';
		$source_array['source']   = isset( $_GET['utm_source'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_source'] ) ) : null;
		$source_array['campaign'] = isset( $_GET['utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_campaign'] ) ) : null;
		$source_array['term']     = isset( $_GET['utm_term'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_term'] ) ) : null;
		$source_array['content']  = isset( $_GET['utm_content'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_content'] ) ) : null;
	} elseif ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
		$referrer_url = sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
		$referrer     = wp_parse_url( $referrer_url, PHP_URL_HOST );
		$host         = wp_parse_url( home_url(), PHP_URL_HOST );

		if ( $referrer && strtolower( $host ) !== strtolower( $referrer ) ) {
			$source_array['source'] = $referrer;
			$source_array['medium'] = 'referral';

			foreach ( $search_systems as $search_system ) {
				if ( preg_match( '/(^|\.)' . preg_quote( $search_system, '/' ) . '\.[a-z.]+$/i', $referrer ) ) {
					$source_array['source'] = $search_system;
					$source_array['medium'] = 'organic';
					break;
				}
			}
		} else {
			$source_array['source'] = 'none';
			$source_array['medium'] = 'direct';
		}
	} else {
		$source_array['source'] = 'none';
		$source_array['medium'] = 'direct';
	}
	// phpcs:enable WordPress.Security.NonceVerification.Recommended

	if ( headers_sent() ) {
		return;
	}

	setcookie( 'source_cookie', wp_json_encode( $source_array ), time() + MONTH_IN_SECONDS, '/' );

	$traffic_summary  = "Источник - {$source_array['source']}, Канал - {$source_array['medium']}";
	$traffic_summary .= ! empty( $source_array['campaign'] ) ? ", Кампания - {$source_array['campaign']}" : '';
	$traffic_summary .= ! empty( $source_array['term'] ) ? ", Объявление - {$source_array['term']}" : '';
	$traffic_summary .= ! empty( $source_array['content'] ) ? ", Ключевое слово - {$source_array['content']}" : '';

	setcookie( 'traffic_source', $traffic_summary, time() + MONTH_IN_SECONDS, '/' );
	$_COOKIE['source_cookie']  = wp_json_encode( $source_array );
	$_COOKIE['traffic_source'] = $traffic_summary;
}

/** REST_REQUEST is defined after init, so also inspect the incoming URL. */
function adem_is_rest_request() {
	// REST route detection happens before the request has a nonce.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return true;
	}
	if ( isset( $_GET['rest_route'] ) ) {
		return true;
	}
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}
	$path      = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );
	$rest_path = wp_parse_url( rest_url(), PHP_URL_PATH );
	// phpcs:enable WordPress.Security.NonceVerification.Recommended
	return $path && $rest_path && 0 === strpos( $path, rtrim( $rest_path, '/' ) . '/' );
}

/** Return the first recorded source for a saved lead and optional email. */
function adem_get_traffic_summary() {
	if ( empty( $_COOKIE['traffic_source'] ) || ! is_string( $_COOKIE['traffic_source'] ) ) {
		return '';
	}
	return sanitize_text_field( wp_unslash( $_COOKIE['traffic_source'] ) );
}
