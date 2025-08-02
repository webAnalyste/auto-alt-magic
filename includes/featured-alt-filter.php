<?php
// Injection ALT/TITLE sur la featured image via filtre WordPress
add_filter('post_thumbnail_html', function($html, $post_id, $post_thumbnail_id, $size, $attr) {
    // Sécurité : vérifier que $post_id est numérique et valide
    if (!is_numeric($post_id) || $post_id <= 0) return $html;
    $post = get_post($post_id);
    if (!is_object($post) || !($post instanceof WP_Post) || !isset($post->ID)) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('[AAM] Contexte post anormal dans post_thumbnail_html (archive ?): post_id=' . $post_id);
        return $html;
    }
    
    // VÉRIFICATION PRIORITAIRE : Si "Ne pas modifier les ALT de ce contenu" est activé, retourner le HTML natif
    $disable_alt_modification = get_post_meta($post_id, 'aam_disable_alt_modification', true);
    if ($disable_alt_modification === '1') {
        return $html; // Ne rien faire, laisser le HTML natif tranquille
    }
    
    // Si reset natif demandé, ne toucher à rien : retour strict du HTML natif (aucune suppression, aucun nettoyage)
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
