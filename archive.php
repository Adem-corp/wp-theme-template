<?php
/**
 * Posts archive template
 *
 * @package Theme_name
 * @since 1.0.0
 */

// TODO удалить, если не используются архивы.
get_header();

$term_object = get_queried_object();
?>

<?php if ( have_posts() ) : ?>

	<?php the_archive_title( '<h1 class="page-title">', '</h1>' ); ?>
	<?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>

	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>

		<?php the_title(); ?>

	<?php endwhile; ?>

	<?php the_posts_navigation(); ?>

<?php else : ?>

	<div class="archive-empty">Записей нет.</div>

<?php endif; ?>

<?php
if ( is_category() && $term_object instanceof WP_Term ) {
	get_template_part(
		'layouts/partials/blocks',
		null,
		array(
			'id' => 'category_' . $term_object->term_id,
		)
	);
}

get_footer();
