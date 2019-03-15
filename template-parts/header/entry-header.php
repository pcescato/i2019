<?php
/**
 * Displays the post header
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */

$discussion = ! is_page() && twentynineteen_can_show_post_thumbnail() ? twentynineteen_get_discussion_data() : null; ?>

<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

<?php if ( ! is_page() ) : ?>
    <?php
    $remove_author = get_theme_mod('i2019_remove_author');
    $remove_date = get_theme_mod('i2019_remove_date');
    $remove_comments = get_theme_mod('i2019_remove_comments');
    ?>
<?php if( ($remove_author + $remove_comments + $remove_date) != 0 || current_user_can('edit_posts') ) ?>
<div class="entry-meta">
	<?php if($remove_author == 0) twentynineteen_posted_by(); ?>
	<?php if($remove_date == 0) i2019_posted_on(); ?>
        <?php if($remove_comments == 0): ?>
	<span class="comment-count">
		<?php
		if ( ! empty( $discussion ) ) {
			twentynineteen_discussion_avatars_list( $discussion->authors );
		}
		?>
		<?php twentynineteen_comment_count(); ?>
	</span>
        <?php endif; ?>
	<?php
	// Edit post link.
		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers. */
					__( 'Edit <span class="screen-reader-text">%s</span>', 'i2019' ),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				get_the_title()
			),
			'<span class="edit-link">' . twentynineteen_get_icon_svg( 'edit', 16 ),
			'</span>'
		);
	?>
</div><!-- .meta-info -->
<?php endif; ?>
