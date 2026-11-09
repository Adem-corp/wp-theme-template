<?php
/**
 * Neutral example layout. Create a matching ACF Flexible Content layout
 * named "example" with a text subfield named "heading".
 *
 * @package Theme_name
 * @since 1.0.0
 */

$heading = function_exists( 'get_sub_field' ) ? get_sub_field( 'heading' ) : '';
if ( $heading ) :
	?>
	<section class="example-block">
		<div class="container">
			<h2 class="example-block__title"><?php echo esc_html( $heading ); ?></h2>
		</div>
	</section>
	<?php
endif;
