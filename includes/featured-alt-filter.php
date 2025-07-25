<?php
// Injection ALT/TITLE sur la featured image via filtre WordPress
add_filter('post_thumbnail_html', function($html, $post_id, $post_thumbnail_id, $size, $attr) {
    $post = get_post($post_id);
    if (!$post) return $html;
    // Si reset natif demandé, ne toucher à rien : retour strict du HTML natif (aucune suppression, aucun nettoyage)
    if (isset($_POST['aam_reset_native_alt']) && $_POST['aam_reset_native_alt'] == '1') {
        return $html;
    }
    // Désactiver toute modification si le mode global est 'none' : retour strict du HTML natif, aucun traitement
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    if ($alt_replace_mode === 'none') {
        return $html;
    }
    $manual_alt = get_post_meta($post_id, 'aam_featured_alt', true);
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    // Si aucun ALT custom/plugin, laisser le alt natif (ne pas injecter get_the_title)
    if (!empty($manual_alt)) {
        // Injection dans le HTML
        $html = preg_replace('/alt=(["\\"]).*?\\1/', 'alt="' . esc_attr($manual_alt) . '"', $html);
        // Synchroniser title si absent ou vide
        if (strpos($html, 'title=') === false) {
            $html = preg_replace('/<img/i', '<img title="' . esc_attr($manual_alt) . '"', $html);
        }
    }
    return $html;
}, 10, 5);
