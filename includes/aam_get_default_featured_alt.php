<?php
/**
 * Retourne la valeur ALT par défaut pour la featured image d'un post,
 * selon les réglages globaux du type de contenu (template/tag).
 * Utilisé comme fallback si aucun ALT manuel n'est saisi.
 * @param WP_Post $post
 * @return string
 */
function aam_get_default_featured_alt($post) {
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $method = $type_settings['method'] ?? get_option('aam_method', 'titre');
    $text_libre = $type_settings['text_libre'] ?? get_option('aam_text_libre', '');
    $titre = get_the_title($post->ID);
    $mot_cle = get_post_meta($post->ID, 'aam_focus_keyword', true);
    if (!$mot_cle) {
        require_once AAM_PLUGIN_DIR . 'includes/seo.php';
        $mot_cle = aam_get_focus_keyword($post->ID);
    }
    $lang = get_locale();
    $type_post = $type;
    $nom_image = '';
    // Récupérer le nom du fichier de la featured image
    $thumb_id = get_post_thumbnail_id($post->ID);
    if ($thumb_id) {
        $img_url = wp_get_attachment_url($thumb_id);
        if ($img_url) {
            $nom_image = basename($img_url);
        }
    }
    // Génération selon la méthode
    if ($method === 'titre') {
        $alt = $titre;
    } elseif ($method === 'nom_fichier') {
        $alt = $nom_image;
    } elseif ($method === 'titre_image') {
        // Récupérer le titre de la featured image (media title)
        if ($thumb_id) {
            $media_title = get_the_title($thumb_id);
            $alt = !empty($media_title) ? $media_title : $titre; // Fallback sur titre du post si vide
        } else {
            $alt = $titre; // Fallback si pas de featured image
        }
    } elseif ($method === 'texte_libre') {
        require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
        $alt = aam_parse_template_tags($text_libre, [
            'mot_cle' => $mot_cle,
            'titre' => $titre,
            'nom_image' => $nom_image,
            'lang' => $lang,
            'type_post' => $type_post,
        ]);
    } else {
        $alt = $titre;
    }
    return trim($alt);
}
