<?php
/**
 * Metabox ALT pour l’image à la une (featured image) dans l’éditeur de post/page
 * Permet une édition manuelle de l’ALT spécifique à ce post
 */
if (!defined('ABSPATH')) exit;

add_action('add_meta_boxes', function() {
    $types = apply_filters('aam_featured_alt_post_types', ['post','page']);
    foreach ($types as $type) {
        add_meta_box(
            'aam_featured_alt',
            __('ALT image à la une', 'auto-alt-magic'),
            'aam_featured_alt_metabox_render',
            $type,
            'side',
            'default'
        );
    }
});

function aam_featured_alt_metabox_render($post) {
    $thumb_id = get_post_thumbnail_id($post->ID);
    $custom_alt = get_post_meta($post->ID, '_aam_featured_alt', true);
    $media_alt = $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '';
    echo '<p><strong>' . __('ALT spécifique à ce post', 'auto-alt-magic') . '</strong></p>';
    echo '<input type="text" name="aam_featured_alt" value="' . esc_attr($custom_alt ?: $media_alt) . '" style="width:100%" maxlength="255" placeholder="ALT unique pour ce post" />';
    if ($thumb_id) {
        echo '<p style="font-size:11px;color:#888">' . __('ALT global (média) : ', 'auto-alt-magic') . esc_html($media_alt) . '</p>';
    }
    wp_nonce_field('aam_featured_alt_nonce', 'aam_featured_alt_nonce_field');
}

add_action('save_post', function($post_id) {
    if (!isset($_POST['aam_featured_alt_nonce_field']) || !wp_verify_nonce($_POST['aam_featured_alt_nonce_field'], 'aam_featured_alt_nonce')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    if (isset($_POST['aam_featured_alt'])) {
        $alt = sanitize_text_field($_POST['aam_featured_alt']);
        update_post_meta($post_id, '_aam_featured_alt', $alt);
    }
}, 20);
