<?php
// Surcharge native ALT/TITLE sur toutes les images (featured, galeries, contenu, WooCommerce...)
// Hook conditionnel : ne s'attacher que si nécessaire
add_action('init', function() {
    add_filter('wp_get_attachment_image_attributes', 'aam_filter_image_attributes', 20, 3);
});

function aam_filter_image_attributes($attr, $attachment, $size) {
    global $post;
    if (!$post || !isset($post->ID)) return $attr;
    
    // SI mode global 'ne rien remplacer' OU reset natif, désactiver complètement le filtre
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    $is_reset = get_post_meta($post->ID, 'aam_reset_native_alt', true);
    
    if ($alt_replace_mode === 'none' || $is_reset === '1') {
        return $attr;
    }
    // ALT manuel (metabox)
    $manual_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    // ALT global (fallback)
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    // Si pas de meta ni de fallback global, NE PAS injecter le titre du post, laisser la valeur native
    // (supprimer ce fallback pour n'injecter que si meta ou réglage global)
    // Appliquer ALT/TITLE uniquement si l’image est la featured ou dans le contenu du post courant
    $thumb_id = get_post_thumbnail_id($post->ID);
    if ($attachment->ID == $thumb_id || strpos($post->post_content, wp_get_attachment_url($attachment->ID)) !== false) {
        $attr['alt'] = esc_attr($manual_alt);
        if (empty($attr['title'])) {
            $attr['title'] = esc_attr($manual_alt);
        }
    }
    return $attr;
}
