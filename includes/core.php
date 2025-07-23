<?php
/**
 * Traite le post_content pour injecter ALT et TITLE dans les balises <img>
 * Appelée sur save_post par auto-alt-magic.php
 * @param int $post_ID
 * @param WP_Post $post
 */
function aam_core_process_post($post_ID, $post) {
    // Traitement image à la une (featured image) pour post/page (hors produit)
    if (in_array($post->post_type, ['post', 'page'])) {
        $thumb_id = get_post_thumbnail_id($post_ID);
        if ($thumb_id) {
            $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
            $method = get_option('aam_method', 'titre');
            $titre = get_the_title($post_ID);
            $nom_image = get_post_field('post_name', $thumb_id);
            $mot_cle = aam_get_focus_keyword($post_ID);
            if (!$mot_cle) {
                $mot_cle = get_post_meta($post_ID, 'aam_focus_keyword', true);
            }
            $lang = get_locale();
            $type_post = $post->post_type;
            $alt_new = '';
            if ($method === 'titre') {
                $alt_new = $titre;
            } elseif ($method === 'nom_fichier') {
                $alt_new = $nom_image;
            } elseif ($method === 'texte_libre') {
                require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
                $alt_new = aam_parse_template_tags(get_option('aam_text_libre', ''), [
                    'mot_cle' => $mot_cle,
                    'titre' => $titre,
                    'nom_image' => $nom_image,
                    'lang' => $lang,
                    'type_post' => $type_post,
                ]);
            }
            // Hook développeur : personnalisation du texte ALT généré (featured image post/page)
            $alt_new = apply_filters('autoalt_custom_alt', $alt_new, '', $post_ID, [
                'method' => $method,
                'mot_cle' => $mot_cle,
                'titre' => $titre,
                'nom_image' => $nom_image,
                'lang' => $lang,
                'type_post' => $type_post,
            ]);
            if (!empty($alt_new)) {
                update_post_meta($thumb_id, '_wp_attachment_image_alt', esc_attr($alt_new));
            }
        }
    }
    // WooCommerce : traiter aussi la galerie et le thumbnail si produit
    if ($post->post_type === 'product' && function_exists('wc_get_product')) {
        $product = wc_get_product($post_ID);
        if ($product) {
            // Thumbnail principal
            $thumb_id = $product->get_image_id();
            if ($thumb_id) {
                $alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                $method = get_option('aam_method', 'titre');
                $titre = get_the_title($post_ID);
                $nom_image = get_post_field('post_name', $thumb_id);
                $mot_cle = aam_get_focus_keyword($post_ID);
                if (!$mot_cle) {
                    $mot_cle = get_post_meta($post_ID, 'aam_focus_keyword', true);
                }
                $lang = get_locale();
                $type_post = $post->post_type;
                $alt_new = '';
                if ($method === 'titre') {
                    $alt_new = $titre;
                } elseif ($method === 'nom_fichier') {
                    $alt_new = $nom_image;
                } elseif ($method === 'texte_libre') {
                    require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
                    $alt_new = aam_parse_template_tags(get_option('aam_text_libre', ''), [
                        'mot_cle' => $mot_cle,
                        'titre' => $titre,
                        'nom_image' => $nom_image,
                        'lang' => $lang,
                        'type_post' => $type_post,
                    ]);
                }
                /**
                 * Hook développeur : personnalisation du texte ALT généré (WooCommerce thumbnail)
                 * Voir doc bloc principal.
                 */
                $alt_new = apply_filters('autoalt_custom_alt', $alt_new, '', $post_ID, [
                    'method' => $method,
                    'mot_cle' => $mot_cle,
                    'titre' => $titre,
                    'nom_image' => $nom_image,
                    'lang' => $lang,
                    'type_post' => $type_post,
                ]);
                if (!empty($alt_new)) {
                    update_post_meta($thumb_id, '_wp_attachment_image_alt', esc_attr($alt_new));
                }
            }
            // Galerie produit
            $gallery_ids = $product->get_gallery_image_ids();
            if (is_array($gallery_ids)) {
                foreach ($gallery_ids as $img_id) {
                    $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
                    $method = get_option('aam_method', 'titre');
                    $titre = get_the_title($post_ID);
                    $nom_image = get_post_field('post_name', $img_id);
                    $mot_cle = aam_get_focus_keyword($post_ID);
                    if (!$mot_cle) {
                        $mot_cle = get_post_meta($post_ID, 'aam_focus_keyword', true);
                    }
                    $lang = get_locale();
                    $type_post = $post->post_type;
                    $alt_new = '';
                    if ($method === 'titre') {
                        $alt_new = $titre;
                    } elseif ($method === 'nom_fichier') {
                        $alt_new = $nom_image;
                    } elseif ($method === 'texte_libre') {
                        require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
                        $alt_new = aam_parse_template_tags(get_option('aam_text_libre', ''), [
                            'mot_cle' => $mot_cle,
                            'titre' => $titre,
                            'nom_image' => $nom_image,
                            'lang' => $lang,
                            'type_post' => $type_post,
                        ]);
                    }
                    /**
                     * Hook développeur : personnalisation du texte ALT généré (WooCommerce galerie)
                     * Voir doc bloc principal.
                     */
                    $alt_new = apply_filters('autoalt_custom_alt', $alt_new, '', $post_ID, [
                        'method' => $method,
                        'mot_cle' => $mot_cle,
                        'titre' => $titre,
                        'nom_image' => $nom_image,
                        'lang' => $lang,
                        'type_post' => $type_post,
                    ]);
                    if (!empty($alt_new)) {
                        update_post_meta($img_id, '_wp_attachment_image_alt', esc_attr($alt_new));
                    }
                }
            }
        }
    }

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
        /**
         * Hook développeur : personnalisation du texte ALT généré.
         *
         * @param string $alt     ALT généré par Auto ALT Magic
         * @param string $src     URL de l'image
         * @param int    $post_ID ID du post
         * @param array  $context [méthode, mot_cle, titre, nom_image, lang, type_post]
         * @return string         ALT personnalisé
         *
         * Exemple d'usage dans functions.php :
         * add_filter('autoalt_custom_alt', function($alt, $src, $post_id, $context) {
         *     if ($context['type_post']==='product') return $alt.' - Produit';
         *     return $alt;
         * }, 10, 4);
         */
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
