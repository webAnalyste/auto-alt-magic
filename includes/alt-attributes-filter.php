<?php
// Surcharge native ALT/TITLE sur toutes les images (featured, galeries, contenu, WooCommerce...)
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    global $post;
    if (!$post || !isset($post->ID)) return $attr;
    // Si demande explicite de reset natif OU mode "ne rien remplacer" (aam_alt_replace_mode = 'none'), ne rien surcharger
    $alt_replace_mode = get_post_meta($post->ID, 'aam_alt_replace_mode', true);
    if ((isset($_POST['aam_reset_native_alt']) && $_POST['aam_reset_native_alt'] == '1') || $alt_replace_mode === 'none') {
        // Forcer retour strict à la valeur native (aucune injection ni fallback)
        unset($attr['alt']);
        unset($attr['title']);
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
}, 20, 3);
