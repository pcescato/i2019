<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

if (!function_exists('i2019_locale_css')):

    function i2019_locale_css($uri) {
        if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }

endif;
add_filter('locale_stylesheet_uri', 'i2019_locale_css');

function i2019_theme_setup() {
    load_child_theme_textdomain('i2019', get_stylesheet_directory() . '/languages');
}

add_action('after_setup_theme', 'i2019_theme_setup');

require_once get_stylesheet_directory() . '/inc/class-tgm-plugin-activation.php';
require_once get_stylesheet_directory() . '/inc/required-plugins.php';

if (!function_exists('i2019_parent_css')):

    function i2019_parent_css() {
        wp_enqueue_style('i2019_parent', trailingslashit(get_template_directory_uri()) . 'style.css', array());
    }

endif;

add_action('wp_enqueue_scripts', 'i2019_parent_css', 10);
add_action('save_post', 'i2019_reading_time', 10, 3);
add_action('customize_register', 'i2019_customize_register');
add_action('wp_enqueue_scripts', 'i2019_custom_styles');

if (get_theme_mod('i2019_infinite_scroll') == 1)
    add_action('after_setup_theme', 'i2019_infinite_scroll');

function i2019_customize_register($wp_customize) {

    $wp_customize->add_panel('i2019_options', array(
        'priority' => 1,
        'theme_supports' => '',
        'title' => __('i2019 Options', 'i2019'),
        'description' => __('i2019 Theme Options.', 'i2019'),
    ));

    $wp_customize->add_section(
            'i2019_footer_features', array(
        'title' => __('Footer Features', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 7,
        'capability' => 'edit_theme_options',
        'description' => __('Change Footer Features Here.', 'i2019'),
            )
    );

    $wp_customize->add_section(
            'i2019_infinite_scroll', array(
        'title' => __('Infinite Scroll', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 6,
        'capability' => 'edit_theme_options',
        'description' => __('Activate infinite scroll.', 'i2019'),
            )
    );
    $wp_customize->add_section(
            'i2019_css_tweaks', array(
        'title' => __('CSS Tweaks', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 2,
        'capability' => 'edit_theme_options',
        'description' => __('All i2019 Theme CSS tweaks.', 'i2019'),
            )
    );

    $wp_customize->add_section(
            'i2019_metas_archive', array(
        'title' => __('Archives Metas', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 2,
        'capability' => 'edit_theme_options',
        'description' => __('Keep or remove Post Metas from Archives Pages.', 'i2019'),
            )
    );

    $wp_customize->add_section(
            'i2019_content_single', array(
        'title' => __('Single Post Content', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 3,
        'capability' => 'edit_theme_options',
        'description' => __('Add extra content to single post.', 'i2019'),
            )
    );

    $wp_customize->add_setting('i2019_infinite_scroll', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_infinite_scroll', array(
        'label' => __('Activate infinite scroll when checked. You must enable Infinite scroll in Jetpack > Settings > Writing: Load more posts in page with a button', 'i2019'),
        'section' => 'i2019_infinite_scroll',
        'settings' => 'i2019_infinite_scroll',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_footer_menu_right', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_footer_menu_right', array(
        'label' => __('Right Align Footer Menu', 'i2019'),
        'section' => 'i2019_footer_features',
        'settings' => 'i2019_footer_menu_right',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_footer_content', array(
        'default' => '',
        'sanitize_callback' => 'wp_filter_nohtml_kses'
            )
    );

    $wp_customize->add_control('i2019_footer_content', array(
        'label' => __('Footer Content', 'i2019'),
        'section' => 'i2019_footer_features',
        'settings' => 'i2019_footer_content',
        'type' => 'text',
        'priority' => 6
    ));

    $wp_customize->add_setting('i2019_show_excerpt', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_show_excerpt', array(
        'label' => __('Display excerpt above content', 'i2019'),
        'section' => 'i2019_content_single',
        'settings' => 'i2019_show_excerpt',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_words_count', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_words_count', array(
        'label' => __('Display post words count', 'i2019'),
        'section' => 'i2019_content_single',
        'settings' => 'i2019_words_count',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_show_time_to_read', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_show_time_to_read', array(
        'label' => __('Display post time to read', 'i2019'),
        'section' => 'i2019_content_single',
        'settings' => 'i2019_show_time_to_read',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_section(
            'i2019_metas_single', array(
        'title' => __('Single Post Metas', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 2,
        'capability' => 'edit_theme_options',
        'description' => __('Keep or remove Post Metas from Single Post.', 'i2019'),
            )
    );

    $wp_customize->add_section(
            'i2019_scroll', array(
        'title' => __('Scroll to Top', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 2,
        'capability' => 'edit_theme_options',
        'description' => __('Add a Scroll to Top button.', 'i2019'),
            )
    );

    $wp_customize->add_setting('i2019_scroll_to_top', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_scroll_to_top', array(
        'label' => __('Scroll to Top Button', 'i2019'),
        'section' => 'i2019_scroll',
        'settings' => 'i2019_scroll_to_top',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_full_width', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_full_width', array(
        'label' => __('Make Content Full Width', 'i2019'),
        'section' => 'i2019_css_tweaks',
        'settings' => 'i2019_full_width',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_featured_margin', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_featured_margin', array(
        'label' => __('Remove featured Image Margin', 'i2019'),
        'section' => 'i2019_css_tweaks',
        'settings' => 'i2019_remove_featured_margin',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_section(
            'i2019_theme_colors', array(
        'title' => __('Theme Colors', 'i2019'),
        'panel' => 'i2019_options',
        'priority' => 0,
        'capability' => 'edit_theme_options',
        'description' => __('Define colors for text and background.', 'i2019'),
            )
    );

    $wp_customize->add_setting('i2019_text_color', array(
        'default' => '#11171e'
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'i2019_text_color', array(
        'section' => 'i2019_theme_colors',
        'label' => esc_html__('Text color', 'i2019'),
    )));

    $wp_customize->add_setting('i2019_background_color', array(
        'default' => '#f5f9fc'
    ));

    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'i2019_background_color', array(
        'section' => 'i2019_theme_colors',
        'label' => esc_html__('Background color', 'i2019'),
    )));

    $wp_customize->add_setting('i2019_separators', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_separators', array(
        'label' => __('Set Separators between Menu Items', 'i2019'),
        'section' => 'i2019_css_tweaks',
        'settings' => 'i2019_separators',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_drop_logo_border', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_drop_logo_border', array(
        'label' => __('Remove Border around Logo', 'i2019'),
        'section' => 'i2019_css_tweaks',
        'settings' => 'i2019_drop_logo_border',
        'type' => 'checkbox',
        'priority' => 1
    ));


    $wp_customize->add_setting('i2019_remove_author', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_author', array(
        'label' => __('Remove Author Post Meta', 'i2019'),
        'section' => 'i2019_metas_single',
        'settings' => 'i2019_remove_author',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_date', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_date', array(
        'label' => __('Remove Date Post Meta', 'i2019'),
        'section' => 'i2019_metas_single',
        'settings' => 'i2019_remove_date',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_comments', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_comments', array(
        'label' => __('Remove Comments Post Meta', 'i2019'),
        'section' => 'i2019_metas_single',
        'settings' => 'i2019_remove_comments',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_author_from_archive', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_author_from_archive', array(
        'label' => __('Remove Author Post Meta', 'i2019'),
        'section' => 'i2019_metas_archive',
        'settings' => 'i2019_remove_author_from_archive',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_date_from_archive', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_date_from_archive', array(
        'label' => __('Remove Date Post Meta', 'i2019'),
        'section' => 'i2019_metas_archive',
        'settings' => 'i2019_remove_date_from_archive',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_remove_comments_from_archive', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_remove_comments_from_archive', array(
        'label' => __('Remove Comments Post Meta', 'i2019'),
        'section' => 'i2019_metas_archive',
        'settings' => 'i2019_remove_comments_from_archive',
        'type' => 'checkbox',
        'priority' => 1
    ));

    $wp_customize->add_setting('i2019_top_archives', array(
        'default' => 0,
        'sanitize_callback' => 'sanitize_checkbox'
            )
    );

    $wp_customize->add_control('i2019_top_archives', array(
        'label' => __('Remove Space After Title', 'i2019'),
        'section' => 'i2019_css_tweaks',
        'settings' => 'i2019_top_archives',
        'type' => 'checkbox',
        'priority' => 1
    ));
}

function sanitize_checkbox($input) {
    return ( $input === true ) ? true : false;
}

function i2019_custom_styles() {

    $custom_style = 'body {color:' . get_theme_mod('i2019_text_color', '#11171e') . '; background-color:' . get_theme_mod('i2019_background_color', '#f5f9fc') . '}';

    $custom_style .= (1 != get_theme_mod('i2019_scroll_to_top')) ? '' : 'html{scroll-behavior:smooth}body{position:relative}.scrolltop-wrap{box-sizing:border-box;position:absolute;top:12rem;right:2rem;bottom:0;pointer-events:none;-webkit-backface-visibility:hidden;backface-visibility:hidden}.scrolltop-wrap #scrolltop-bg{fill:currentcolor}.scrolltop-wrap #scrolltop-arrow{fill:white}.scrolltop-wrap a:hover #scrolltop-bg{fill:currentcolor}.scrolltop-wrap a:hover #scrolltop-arrow{fill:white}@supports (-moz-appearance:meterbar){.scrolltop-wrap{clip:rect(0,3rem,auto,0)}}.scrolltop-wrap a{position:fixed;position:-webkit-sticky;position:sticky;top:-5rem;margin-bottom:-5rem;-webkit-transform:translateY(100vh);transform:translateY(100vh);-webkit-backface-visibility:hidden;backface-visibility:hidden;display:inline-block;text-decoration:none;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;pointer-events:all;outline:none;overflow:hidden}.scrolltop-wrap a svg{display:block;border-radius:50%;width:100%;height:100%}.scrolltop-wrap a svg path{transition:all 0.1s}.scrolltop-wrap a #scrolltop-arrow{-webkit-transform:scale(.66);transform:scale(.66);-webkit-transform-origin:center;transform-origin:center}@media print{.scrolltop-wrap{display:none!important}}';

    $custom_style .= (1 != get_theme_mod('i2019_full_width')) ? "" : ".woocommerce .content-area .site-main, .entry .entry-content > *,
  .entry .entry-summary > *, .comments-area {
  max-width: none;
}
.entry .entry-content .wp-block-image .aligncenter {
  max-width: 100%;
  width: 100%;
}
";
$custom_style .= (1 != get_theme_mod('i2019_footer_menu_right')) ? "" : '.footer-navigation > div {
    float: right;
}';

$custom_style .= (1 != get_theme_mod('i2019_drop_logo_border')) ? "" : '.site-header.featured-image .custom-logo-link:hover, .site-header.featured-image .custom-logo-link:active, .site-header.featured-image .custom-logo-link:focus,
.site-logo .custom-logo-link:hover, .site-logo .custom-logo-link:active, .site-logo .custom-logo-link:focus {
  box-shadow: none;
}';

$custom_style .= (1 != get_theme_mod('i2019_separators')) ? "" : 'ul.main-menu > li:not(:last-child):after {
content: "|";
padding-right: 5px;
color: currentcolor;
}';
    $custom_style .= (1 != get_theme_mod('i2019_top_archives')) ? "" : '.archive .page-header, .search .page-header, .error404 .page-header {
  margin: 0 calc(10% + 60px) 0 calc(10% + 60px);
}';
    $custom_style .= (1 != get_theme_mod('i2019_remove_featured_margin')) ? "" : '.site-header.featured-image { margin-bottom: 0 }';

    wp_register_style('i2019-css', false);
    wp_enqueue_style('i2019-css');
    wp_add_inline_style('i2019-css', $custom_style);
}

if (!function_exists('i2019_entry_footer')) :

    /**
     * Prints HTML with meta information for the categories, tags and comments.
     */
    function i2019_entry_footer() {

        $remove_author_from_archive = get_theme_mod('i2019_remove_author_from_archive');
        $remove_date_from_archive = get_theme_mod('i2019_remove_date_from_archive');
        $remove_comments_from_archive = get_theme_mod('i2019_remove_comments_from_archive');

        $remove_author = get_theme_mod('i2019_remove_author');
        $remove_date = get_theme_mod('i2019_remove_date');
        $remove_comments = get_theme_mod('i2019_remove_comments');

        // Hide author, post date, category and tag text for pages.
        if ('post' === get_post_type()) {

            // Posted by
            if (!is_single() && $remove_author_from_archive == 0)
                twentynineteen_posted_by();

            if (is_single() && $remove_author == 0)
                twentynineteen_posted_by();

            // Posted on
            if (!is_single() && $remove_date_from_archive == 0)
                twentynineteen_posted_on();

            if (is_single() && $remove_date == 0)
                twentynineteen_posted_on();

            /* translators: used between list items, there is a space after the comma. */
            $categories_list = get_the_category_list(__(', ', 'i2019'));
            if ($categories_list) {
                printf(
                        /* translators: 1: SVG icon. 2: posted in label, only visible to screen readers. 3: list of categories. */
                        '<span class="cat-links">%1$s<span class="screen-reader-text">%2$s</span>%3$s</span>', twentynineteen_get_icon_svg('archive', 16), __('Posted in', 'i2019'), $categories_list
                ); // WPCS: XSS OK.
            }

            /* translators: used between list items, there is a space after the comma. */
            $tags_list = get_the_tag_list('', __(', ', 'i2019'));
            if ($tags_list) {
                printf(
                        /* translators: 1: SVG icon. 2: posted in label, only visible to screen readers. 3: list of tags. */
                        '<span class="tags-links">%1$s<span class="screen-reader-text">%2$s </span>%3$s</span>', twentynineteen_get_icon_svg('tag', 16), __('Tags:', 'i2019'), $tags_list
                ); // WPCS: XSS OK.
            }
        }

        // Comment count.
        if (!is_singular() && $remove_comments_from_archive == 0) {
            twentynineteen_comment_count();
        }

        // Edit post link.
        edit_post_link(
                sprintf(
                        wp_kses(
                                /* translators: %s: Name of current post. Only visible to screen readers. */
                                __('Edit <span class="screen-reader-text">%s</span>', 'i2019'), array(
            'span' => array(
                'class' => array(),
            ),
                                )
                        ), get_the_title()
                ), '<span class="edit-link">' . twentynineteen_get_icon_svg('edit', 16), '</span>'
        );
    }

endif;

function i2019_reading_time($post_id, $post, $update) {

    if (!$update || wp_is_post_revision($post_id) || $post->post_type != 'post' || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )) {
        return;
    }

    $theContent = str_replace('[', '<', $post->post_content);
    $theContent = str_replace(']', '>', $theContent);
    $word_count = count(explode(' ', strip_tags($theContent)));
    $minutes = ceil($word_count / 250);
    update_post_meta($post_id, 'i2019_words_in_post', $word_count);
    update_post_meta($post_id, 'i2019_time_needed_to_read', $minutes);
}

function i2019_infinite_scroll() {
    add_theme_support('infinite-scroll', array(
        'container' => 'main',
    ));
}

function i2019_display_post_extra_metas() {

    if (get_theme_mod('i2019_show_time_to_read') == 1) :
        $ttr = get_post_meta(get_the_ID(), 'i2019_time_needed_to_read', true);
        $pluralform = ($ttr > 1) ? 's' : '';
    endif;
    if (get_theme_mod('i2019_words_count') == 1):
        $wdc = get_post_meta(get_the_ID(), 'i2019_words_in_post', true);
        $wordscount = ( true === is_array($wdc) ) ? $wdc[0] : $wdc;
    endif;

    if (isset($ttr) && isset($wdc)):
        $output = sprintf(__('<i class="i2019-icon-info-circled-alt"></i> %s words - <i class="i2019-icon-stopwatch"></i> Reading time: %s mn', 'i2019'), $wordscount, $ttr);
    elseif (isset($ttr)):
        $output = sprintf(__('<i class="i2019-icon-stopwatch"></i> Reading time: %s mn', 'i2019'), $ttr);
    elseif (isset($wdc)):
        $output = sprintf(__('<i class="i2019-icon-info-circled-alt"></i> %s words', 'i2019'), $wordscount);
    endif;

    return $output;
}
