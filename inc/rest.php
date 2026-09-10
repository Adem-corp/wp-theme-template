<?php
/**
 * REST API settings
 *
 * @package Theme_name
 * @since 1.0.0
 */

remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
remove_action( 'template_redirect', 'rest_output_link_header', 11 );
remove_action( 'xmlrpc_rsd_apis', 'rest_output_rsd', 10 );

/**
 * Namespaces allowed to be publicly accessible via REST API on the frontend.
 *
 * @return array List of allowed REST namespaces.
 */
function adem_get_allowed_rest_namespaces() {
	return array(
		'/file-monitor/v1',
	);
}

add_filter( 'rest_endpoints', 'adem_clear_rest_endpoints' );
/**
 * Filters REST API endpoints to allow only specific custom namespaces on the frontend.
 *
 * Removes all default WordPress REST API routes (e.g., /wp/v2/*, /oembed/*) from the
 * public REST index and returns only the endpoints that match the allowed namespaces.
 * Applies only to non-authenticated requests, so admin plugins relying on REST API
 * inside wp-admin keep working correctly.
 *
 * @param array $endpoints Registered REST API endpoints.
 *
 * @return array Filtered list of endpoints.
 */
function adem_clear_rest_endpoints( $endpoints ) {
	if ( is_user_logged_in() ) {
		return $endpoints;
	}

	$allowed_namespaces = adem_get_allowed_rest_namespaces();
	$allowed            = array();

	foreach ( $allowed_namespaces as $ns ) {
		foreach ( $endpoints as $route => $details ) {
			if ( 0 === strpos( $route, $ns . '/' ) || $route === $ns ) {
				$allowed[ $route ] = $details;
			}
		}
	}

	return $allowed;
}

add_filter( 'rest_authentication_errors', 'adem_restrict_rest_access' );
/**
 * Restricts REST API access for non-authenticated users to allowed namespaces only.
 *
 * Unlike rest_endpoints (which only hides routes from the REST index), this filter
 * actually blocks execution of the request, returning a 401 error for any route
 * outside the allowed namespaces when the user is not logged in.
 *
 * @param WP_Error|null|true $result Error from another authentication handler,
 *                                   null if no error, or true if authenticated.
 *
 * @return WP_Error|null|true Original result, or WP_Error if access is denied.
 */
function adem_restrict_rest_access( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}

	if ( is_user_logged_in() ) {
		return $result;
	}

	$route = '';

	if ( isset( $GLOBALS['wp']->query_vars['rest_route'] ) ) {
		$route = $GLOBALS['wp']->query_vars['rest_route'];
	} elseif ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$rest_url = wp_parse_url( rest_url(), PHP_URL_PATH );
		$request  = wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH );

		if ( 0 === strpos( $request, $rest_url ) ) {
			$route = substr( $request, strlen( $rest_url ) );
		}
	}

	if ( empty( $route ) ) {
		return $result;
	}

	$allowed_namespaces = adem_get_allowed_rest_namespaces();

	foreach ( $allowed_namespaces as $ns ) {
		if ( 0 === strpos( $route, $ns . '/' ) || $route === $ns ) {
			return $result;
		}
	}

	return new WP_Error( 'rest_forbidden', 'REST API access is restricted.', array( 'status' => 401 ) );
}
