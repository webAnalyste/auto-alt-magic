<?php
/**
 * Retourne la valeur ALT par défaut pour la featured image d'un post,
 * selon les réglages globaux du type de contenu (template/tag).
 * Utilisé comme fallback si aucun ALT manuel n'est saisi.
 * @param WP_Post $post
 * @return string
 */
function aam_get_default_featured_alt($post) {
    // Sécurité : vérifier que $post est bien un objet WP_Post valide
    if (!is_object($post) || !($post instanceof WP_Post) || !isset($post->ID)) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('[AAM] Contexte post anormal dans aam_get_default_featured_alt (archive ?): ' . print_r($post, true));
        return '';
    }
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    // Correction : si tableau vide ou clé manquante, fallback global explicite
    $method = isset($type_settings['method']) && $type_settings['method'] !== '' ? $type_settings['method'] : get_option('aam_method', 'titre');
    $text_libre = isset($type_settings['text_libre']) && $type_settings['text_libre'] !== '' ? $type_settings['text_libre'] : get_option('aam_text_libre', '');
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
    // Préfixe/suffixe
    // Correction forte : si option globale prefix/suffix = 'formation' ou 'expertise', mais champ réglage vide, alors forcer vide
    $prefix_opt = get_option('aam_prefix', '');
    $suffix_opt = get_option('aam_suffix', '');
    if ($prefix_opt === 'formation') $prefix_opt = '';
    if ($suffix_opt === 'expertise') $suffix_opt = '';
    $prefix = isset($type_settings['prefix']) && trim($type_settings['prefix']) !== '' ? trim($type_settings['prefix']) : (trim($prefix_opt) !== '' ? trim($prefix_opt) : '');
    $suffix = isset($type_settings['suffix']) && trim($type_settings['suffix']) !== '' ? trim($type_settings['suffix']) : (trim($suffix_opt) !== '' ? trim($suffix_opt) : '');
    // NE PAS concaténer préfixe/suffixe si vides
    $alt_parts = [];
    if ($prefix !== '') $alt_parts[] = $prefix;
    // $alt sera défini plus bas selon la méthode
    // (on concatène après la génération du $alt principal)

    // Génération selon la méthode
    if ($method === 'titre') {
        $alt = $titre;
    } elseif ($method === 'nom_fichier') {
        $alt = $nom_image;
    } elseif ($method === 'titre_image') {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        $media_title = '';
        if ($thumb_id) {
            $media_title = get_the_title($thumb_id);
        }
        $alt = $media_title !== '' ? $media_title : $titre;
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
    // Appliquer préfixe/suffixe proprement (aucun espace ni texte si vide)
    if ($alt !== '') $alt_parts[] = $alt;
    if ($suffix !== '') $alt_parts[] = $suffix;
    return trim(implode(' ', $alt_parts));
}
