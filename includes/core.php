<?php
/**
 * Traite le post_content pour injecter ALT et TITLE dans les balises <img>
 * Appelée sur save_post par auto-alt-magic.php
 * @param int $post_ID
 * @param WP_Post $post
 */
function aam_core_process_post($post_ID, $post) {
    // Sécurité : ne traiter que les post/page/produit publiés ou en brouillon
    if (!in_array($post->post_type, ['post', 'page', 'product'])) return;
    
    $content = $post->post_content;
    if (empty($content)) return;

    // Charger les réglages
    $method = get_option('aam_method', 'titre');
    $text_libre = get_option('aam_text_libre', '');
    $title_sync = get_option('aam_option_title_sync', 1);

    // Récupérer le focus keyword (SEO ou metabox)
    require_once AAM_PLUGIN_DIR . 'includes/seo.php';
    $mot_cle = aam_get_focus_keyword($post_ID);
    if (!$mot_cle) {
        $mot_cle = get_post_meta($post_ID, 'aam_focus_keyword', true);
    }
    $lang = get_locale();
    $type_post = $post->post_type;
    $titre = get_the_title($post_ID);

    // Utilisation de DOMDocument
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    $images = $dom->getElementsByTagName('img');
    foreach ($images as $img) {
        $src = $img->getAttribute('src');
        $nom_image = '';
        if ($src) {
            $parts = explode('/', $src);
            $nom_image = pathinfo(end($parts), PATHINFO_FILENAME);
        }
        // Génération du nouvel ALT
        $alt = '';
        if ($method === 'titre') {
            $alt = $titre;
        } elseif ($method === 'nom_fichier') {
            $alt = $nom_image;
        } elseif ($method === 'texte_libre') {
            require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
            $alt = aam_parse_template_tags($text_libre, [
                'mot_cle' => $mot_cle,
                'titre' => $titre,
                'nom_image' => $nom_image,
                'lang' => $lang,
                'type_post' => $type_post,
            ]);
        }
        $alt = apply_filters('autoalt_custom_alt', $alt, $src, $post_ID, [
            'method' => $method,
            'mot_cle' => $mot_cle,
            'titre' => $titre,
            'nom_image' => $nom_image,
            'lang' => $lang,
            'type_post' => $type_post,
        ]);
        if (!empty($alt)) {
            $img->setAttribute('alt', esc_attr($alt));
            // Dupliquer le alt en title si demandé
            if ($title_sync && !$img->hasAttribute('title')) {
                $img->setAttribute('title', esc_attr($alt));
            }
        }
    }
    $new_content = $dom->saveHTML();
    // Nettoyage de l’entête XML ajouté par DOMDocument
    $new_content = preg_replace('/^<\?xml.*?\?>/', '', $new_content);
    // Mise à jour du contenu uniquement si modifié
    if ($new_content !== $content) {
        // Sécurité : update_post_meta pour éviter boucle
        
        // Désactive temporairement le hook save_post pour éviter boucle infinie
        remove_action('save_post', 'aam_process_post_content', 20);
        wp_update_post([
            'ID' => $post_ID,
            'post_content' => $new_content
        ]);
        add_action('save_post', 'aam_process_post_content', 20, 3);
    }
}
