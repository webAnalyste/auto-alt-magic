<?php
// Surcharge native ALT/TITLE sur toutes les images (featured, galeries, contenu, WooCommerce...)
// Hook conditionnel : ne s'attacher que si nécessaire
add_action('init', function() {
    add_filter('wp_get_attachment_image_attributes', 'aam_filter_image_attributes', 20, 3);
    
    // FILTRES WOOCOMMERCE OFFICIELS selon le code source
    if (class_exists('WooCommerce')) {
        // Featured image
        add_filter('woocommerce_single_product_image_thumbnail_html', 'aam_filter_woocommerce_image_html', 10, 2);
        // Galerie d'images (thumbnails)
        add_filter('woocommerce_gallery_image_html_attachment_image_params', 'aam_filter_woocommerce_gallery_params', 10, 4);
    }
});

function aam_filter_image_attributes($attr, $attachment, $size) {
    global $post;
    if (!is_object($post) || !($post instanceof WP_Post) || !isset($post->ID)) return $attr;
    if (!is_object($attachment) || !isset($attachment->ID)) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('[AAM] Contexte attachment anormal dans aam_filter_image_attributes (archive ?): ' . print_r($attachment, true));
        return $attr;
    }
    
    // VÉRIFICATION PRIORITAIRE : Si "Ne pas modifier les ALT de ce contenu" est activé, désactiver le filtre
    $disable_alt_modification = get_post_meta($post->ID, 'aam_disable_alt_modification', true);
    if ($disable_alt_modification === '1') {
        return $attr; // Ne rien faire, laisser les ALT natifs tranquilles
    }
    
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

/**
 * Filtre WooCommerce officiel pour les images produit
 * Restaure les ALT natifs si l'option est activée
 */
function aam_filter_woocommerce_image_html($html, $post_thumbnail_id) {
    global $post;
    if (!is_object($post) || !isset($post->ID) || $post->post_type !== 'product') {
        return $html;
    }
    
    // Vérifier si l'option de désactivation est activée
    $disable_alt_modification = get_post_meta($post->ID, 'aam_disable_alt_modification', true);
    if ($disable_alt_modification === '1') {
        // Récupérer l'ALT natif de l'image
        $native_alt = get_post_meta($post_thumbnail_id, '_wp_attachment_image_alt', true);
        
        // Remplacer l'ALT dans le HTML par l'ALT natif
        $html = preg_replace('/alt="[^"]*"/', 'alt="' . esc_attr($native_alt) . '"', $html);
        // Supprimer l'attribut title
        $html = preg_replace('/title="[^"]*"/', '', $html);
    }
    
    return $html;
}

/**
 * Filtre WooCommerce pour les paramètres des images de galerie
 * Restaure les ALT natifs si l'option est activée
 */
function aam_filter_woocommerce_gallery_params($params, $attachment_id, $image_size, $main_image) {
    global $post;
    if (!is_object($post) || !isset($post->ID) || $post->post_type !== 'product') {
        return $params;
    }
    
    // Vérifier si l'option de désactivation est activée
    $disable_alt_modification = get_post_meta($post->ID, 'aam_disable_alt_modification', true);
    if ($disable_alt_modification === '1') {
        // Récupérer l'ALT natif de l'image
        $native_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true);
        
        // Forcer l'ALT natif dans les paramètres
        $params['alt'] = $native_alt ? $native_alt : '';
        // Supprimer l'attribut title
        unset($params['title']);
    }
    
    return $params;
}
