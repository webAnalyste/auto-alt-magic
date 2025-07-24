<?php
// Surcharge native ALT/TITLE sur toutes les images (featured, galeries, contenu, WooCommerce...)
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    global $post;
    if (!$post || !isset($post->ID)) return $attr;
    // ALT manuel (metabox)
    $manual_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    // ALT global (fallback)
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    if (empty($manual_alt)) {
        $manual_alt = get_the_title($post->ID);
    }
    // Appliquer ALT/TITLE uniquement si l’image est la featured ou dans le contenu du post courant
    $thumb_id = get_post_thumbnail_id($post->ID);
    if ($attachment->ID == $thumb_id || strpos($post->post_content, wp_get_attachment_url($attachment->ID)) !== false) {
        $attr['alt'] = esc_attr($manual_alt);
        if (empty($attr['title'])) {
            $attr['title'] = esc_attr($manual_alt);
        }
    }
    return $attr;
}, 20, 3);
