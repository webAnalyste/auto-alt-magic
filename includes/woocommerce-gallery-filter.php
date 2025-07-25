<?php
// Hook spécifique WooCommerce pour galerie produit (featured image + images supplémentaires)

// Hook pour les attributs d'image dans les galeries WooCommerce
add_filter('woocommerce_single_product_image_thumbnail_html', function($html, $attachment_id) {
    global $post;
    if (!$post || $post->post_type !== 'product') return $html;
    
    // SI mode global 'ne rien remplacer' OU reset natif, ne RIEN toucher : retour strict natif
    $type_settings = get_option('aam_settings_product', []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    $is_reset = get_post_meta($post->ID, 'aam_reset_native_alt', true);
    
    if ($alt_replace_mode === 'none' || $is_reset === '1') {
        return $html;
    }
    
    // Logique d'injection ALT/TITLE pour WooCommerce (si mode actif)
    $manual_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    
    if (!empty($manual_alt)) {
        // Injection dans le HTML de la galerie WooCommerce
        $html = preg_replace('/alt=(["\\']).*?\\1/', 'alt="' . esc_attr($manual_alt) . '"', $html);
        if (strpos($html, 'title=') === false) {
            $html = preg_replace('/<img/i', '<img title="' . esc_attr($manual_alt) . '"', $html);
        }
    }
    
    return $html;
}, 10, 2);

// Hook pour l'image principale WooCommerce (featured image dans la galerie)
add_filter('woocommerce_single_product_image_html', function($html, $attachment_id) {
    global $post;
    if (!$post || $post->post_type !== 'product') return $html;
    
    // SI mode global 'ne rien remplacer' OU reset natif, ne RIEN toucher : retour strict natif
    $type_settings = get_option('aam_settings_product', []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    $is_reset = get_post_meta($post->ID, 'aam_reset_native_alt', true);
    
    if ($alt_replace_mode === 'none' || $is_reset === '1') {
        return $html;
    }
    
    // Logique d'injection ALT/TITLE pour WooCommerce (si mode actif)
    $manual_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    
    if (!empty($manual_alt)) {
        // Injection dans le HTML de l'image principale WooCommerce
        $html = preg_replace('/alt=(["\\']).*?\\1/', 'alt="' . esc_attr($manual_alt) . '"', $html);
        if (strpos($html, 'title=') === false) {
            $html = preg_replace('/<img/i', '<img title="' . esc_attr($manual_alt) . '"', $html);
        }
    }
    
    return $html;
}, 10, 2);
