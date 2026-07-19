<?php
/**
 * Title partial template.
 *
 * @package Theme_name
 * @since 1.0.0
 */

if ( ! empty( $args['title']['text'] ) ) {
	$h_class = array(
		'title',
	);

	if ( isset( $args['class'] ) ) {
		$h_class[] = $args['class'];
	}

	echo wp_kses_post(
		sprintf(
			'<%1$s class="%2$s">%3$s</%1$s>',
			$args['title']['type'],
			implode( ' ', $h_class ),
			$args['title']['text']
		)
	);
}
