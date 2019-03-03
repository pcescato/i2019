<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package WordPress
 * @subpackage Twenty_Nineteen
 * @since 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php if (!twentynineteen_can_show_post_thumbnail()) : ?>
        <header class="entry-header">
            <?php get_template_part('template-parts/header/entry', 'header'); ?>
        </header>
        <div class="entry-content">
        <?php endif; ?>
        <div class="entry-content">

            <?php if (get_theme_mod('i2019_words_count') == 1 || get_theme_mod('i2019_show_time_to_read') == 1): ?>
                <!-- googleoff: all --><div><?php echo i2019_display_post_extra_metas(); ?></div><!--googleon: all-->
            <?php endif;
            if (get_theme_mod('i2019_show_excerpt') == 1):
                ?>
                <div class="entry-excerpt">
                    <?php echo get_the_excerpt(); ?> 
                </div>
            <?php endif;
            ?>

            <?php
            the_content(
                    sprintf(
                            wp_kses(
                                    /* translators: %s: Name of current post. Only visible to screen readers */
                                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'i2019'), array(
                'span' => array(
                    'class' => array(),
                ),
                                    )
                            ), get_the_title()
                    )
            );

            wp_link_pages(
                    array(
                        'before' => '<div class="page-links">' . __('Pages:', 'i2019'),
                        'after' => '</div>',
                    )
            );
            ?>
        </div><!-- .entry-content -->

        <footer class="entry-footer">
<?php i2019_entry_footer(); ?>
        </footer><!-- .entry-footer -->

            <?php if (!is_singular('attachment')) : ?>
    <?php get_template_part('template-parts/post/author', 'bio'); ?>
        <?php endif; ?>

</article><!-- #post-${ID} -->
