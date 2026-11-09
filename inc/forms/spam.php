<?php
/**
 * Spam scoring for generic forms.
 *
 * @package Theme_name
 */

/**
 * Detects whether a form submission is likely to be from a bot.
 *
 * Reads time_on_page and typing_speed from the submitted form.
 *
 * @return bool True if the submission is considered suspicious (likely bot), false otherwise.
 */
function adem_is_suspicious_submission() {
	// The AJAX handler validates the form nonce before calling this function.
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	$time_on_page      = isset( $_POST['time_on_page'] ) ? sanitize_text_field( wp_unslash( $_POST['time_on_page'] ) ) : 0;
	$typing_speed_json = $_POST['typing_speed'] ?? '[]'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing.
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	$time_on_page = intval( $time_on_page );
	$typing_speed = json_decode( $typing_speed_json, true );

	if ( $time_on_page < 2000 ) {
		return true;
	}

	$score = 0;

	if ( empty( $typing_speed ) ) {
		if ( $time_on_page < 4000 ) {
			$score += 2;
		}
	} elseif ( count( $typing_speed ) > 5 ) {
		$min      = min( $typing_speed );
		$max      = max( $typing_speed );
		$avg      = array_sum( $typing_speed ) / count( $typing_speed );
		$variance = 0;

		foreach ( $typing_speed as $t ) {
			$variance += pow( $t - $avg, 2 );
		}

		$std_dev  = sqrt( $variance / count( $typing_speed ) );
		$coef_var = $std_dev / ( $avg ? $avg : 1 );

		if ( ( $max - $min ) < 8 ) {
			$score += 2;
		}

		if ( $coef_var < 0.05 ) {
			$score += 2;
		}

		$outliers = array_filter(
			$typing_speed,
			function ( $t ) use ( $avg ) {
				return abs( $t - $avg ) > 50;
			}
		);

		if ( count( $outliers ) === 0 ) {
			++$score;
		}

		$small_threshold = 4;
		$max_chain       = 0;
		$current_chain   = 0;

		foreach ( $typing_speed as $t ) {
			if ( $t <= $small_threshold ) {
				++$current_chain;
			} else {
				$current_chain = 0;
			}

			$max_chain = max( $max_chain, $current_chain );
		}

		if ( $max_chain >= 4 ) {
			$score += 3;
		}

		$chain                 = 1;
		$count_of_typing_speed = count( $typing_speed );

		for ( $i = 1; $i < $count_of_typing_speed; $i++ ) {
			if ( abs( $typing_speed[ $i ] - $typing_speed[ $i - 1 ] ) < 2 ) {
				++$chain;
			} else {
				$chain = 1;
			}

			if ( $chain >= 5 ) {
				$score += 3;
				break;
			}
		}
	}

	$log = array(
		'ip'           => $_SERVER['REMOTE_ADDR'] ?? null, // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.NonceVerification.Missing.
		'date'         => current_time( 'mysql' ),
		'score'        => $score,
		'time_on_page' => $time_on_page,
		'typing_speed' => $typing_speed_json,
		'post'         => $_POST, //phpcs:ignore WordPress.Security.NonceVerification.Missing
	);

	$log_line = wp_json_encode( $log, JSON_UNESCAPED_UNICODE ) . PHP_EOL;

	error_log( $log_line, 3, WP_CONTENT_DIR . '/antibot.log' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log

	return $score >= 4;
}
