<?php
/**
 * Traite le post_content pour injecter ALT et TITLE dans les balises <img>
 * Appelée sur save_post par auto-alt-magic.php
 * @param int $post_ID
 * @param WP_Post $post
 */
function aam_core_process_post($post_ID, $post) {
    // Suppression : ne plus modifier le ALT global dans la médiathèque
    // Toute la logique passe par le parsing du post_content (HTML), ALT contextuel


    // Sécurité : ne traiter que les post/page/produit publiés ou en brouillon
    if (!in_array($post->post_type, ['post', 'page', 'product'])) return;
    
    // VÉRIFICATION PRIORITAIRE : Si "Ne pas modifier les ALT de ce contenu" est activé, désactiver le traitement
    $disable_alt_modification = get_post_meta($post_ID, 'aam_disable_alt_modification', true);
    if ($disable_alt_modification === '1') {
        return;
    }
    
    // SI mode global 'ne rien remplacer' OU reset natif, désactiver complètement le traitement du post_content
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    $is_reset = get_post_meta($post_ID, 'aam_reset_native_alt', true);
    
    if ($alt_replace_mode === 'none' || $is_reset === '1') {
        return;
    }
    
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
    $imgs = $dom->getElementsByTagName('img');
    if (!$imgs || $imgs->length === 0) return;

    // Prépare les paramètres contextuels (meta > réglages type > options globales legacy)
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $method = get_post_meta($post->ID, 'aam_method', true)
        ?: ($type_settings['method'] ?? get_option('aam_method', 'titre'));
    $text_libre = get_post_meta($post->ID, 'aam_text_libre', true)
        ?: ($type_settings['text_libre'] ?? get_option('aam_text_libre', ''));
    $prefix = get_post_meta($post->ID, 'aam_prefix', true)
        ?: ($type_settings['prefix'] ?? get_option('aam_prefix', ''));
    $suffix = get_post_meta($post->ID, 'aam_suffix', true)
        ?: ($type_settings['suffix'] ?? get_option('aam_suffix', ''));
    $only_empty = get_post_meta($post->ID, 'aam_only_empty_alt', true);
    if ($only_empty === '') $only_empty = isset($type_settings['only_empty_alt']) ? $type_settings['only_empty_alt'] : get_option('aam_only_empty_alt', 0);
    $replace_all = get_post_meta($post->ID, 'aam_replace_all_alt', true);
    if ($replace_all === '') $replace_all = isset($type_settings['replace_all_alt']) ? $type_settings['replace_all_alt'] : get_option('aam_replace_all_alt', 0);
    $title_sync = get_post_meta($post->ID, 'aam_option_title_sync', true);
    if ($title_sync === '') $title_sync = isset($type_settings['option_title_sync']) ? $type_settings['option_title_sync'] : get_option('aam_option_title_sync', 1);
    $focus_keyword = aam_get_focus_keyword($post->ID);
    if (!$focus_keyword) {
        $focus_keyword = get_post_meta($post->ID, 'aam_focus_keyword', true);
    }
    $featured_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    $thumb_id = get_post_thumbnail_id($post->ID);
    $thumb_url = $thumb_id ? wp_get_attachment_url($thumb_id) : '';
    $titre = get_the_title($post->ID);
    $lang = get_locale();
    $type_post = $post->post_type;

    // Récupérer le mode de remplacement ALT (réglages globaux par type > meta post > fallback)
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 
        (get_post_meta($post->ID, 'aam_alt_replace_mode', true) ?: 'empty');

    // Si reset natif demandé, nettoyer le HTML : supprimer tous les attributs alt et title custom dans les <img>
    if (isset($_POST['aam_reset_native_alt']) && $_POST['aam_reset_native_alt'] == '1') {
        // Nettoyage du HTML : suppression attributs alt et title sur toutes les <img>
        libxml_use_internal_errors(true);
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $imgs = $dom->getElementsByTagName('img');
        foreach ($imgs as $img) {
            $img->removeAttribute('alt');
            $img->removeAttribute('title');
        }
        $new_content = $dom->saveHTML();
        // Suppression de la meta ALT custom de la featured image
        delete_post_meta($post_ID, 'aam_featured_alt');
        // Mise à jour du post_content nettoyé
        remove_action('save_post', 'aam_process_post_content', 20);
        wp_update_post([
            'ID' => $post_ID,
            'post_content' => $new_content,
        ]);
        add_action('save_post', 'aam_process_post_content', 20, 3);
        return;
    }
    // Parcours toutes les images du contenu
    foreach ($imgs as $img) {
        $src = $img->getAttribute('src');
        $alt = $img->getAttribute('alt');
        $is_featured = ($thumb_url && $src && strpos($src, $thumb_url) !== false);
        $nom_image = '';
        if ($src) {
            $nom_image = pathinfo(parse_url($src, PHP_URL_PATH), PATHINFO_FILENAME);
        }
        // Cas featured image : priorité ALT manuel > ALT global > fallback
        if ($is_featured) {
            if (!empty($featured_alt)) {
                $alt_new = $featured_alt;
            } else {
                // ALT global calculé via aam_get_default_featured_alt
                if (function_exists('aam_get_default_featured_alt')) {
                    $alt_new = aam_get_default_featured_alt($post);
                } else {
                    // fallback logique existante
                    if ($method === 'titre') {
                        $alt_new = $titre;
                    } elseif ($method === 'nom_fichier') {
                        $alt_new = $nom_image;
                    } elseif ($method === 'texte_libre') {
                        require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
                        $alt_new = aam_parse_template_tags($text_libre, [
                            'mot_cle' => $focus_keyword,
                            'titre' => $titre,
                            'nom_image' => $nom_image,
                            'lang' => $lang,
                            'type_post' => $type_post,
                        ]);
                    } else {
                        $alt_new = $titre;
                    }
                }
            }
        } else {
            // Génération selon méthode pour toutes les autres images
            if ($method === 'titre') {
                $alt_new = $titre;
            } elseif ($method === 'nom_fichier') {
                $alt_new = $nom_image;
            } elseif ($method === 'titre_image') {
                // Récupérer le titre de l'image (media title)
                $attachment_id = attachment_url_to_postid($src);
                if ($attachment_id) {
                    $media_title = get_the_title($attachment_id);
                    $alt_new = !empty($media_title) ? $media_title : $titre; // Fallback sur titre du post si vide
                } else {
                    $alt_new = $titre; // Fallback si attachment non trouvé
                }
            } elseif ($method === 'texte_libre') {
                require_once AAM_PLUGIN_DIR . 'includes/template-parser.php';
                $alt_new = aam_parse_template_tags($text_libre, [
                    'mot_cle' => $focus_keyword,
                    'titre' => $titre,
                    'nom_image' => $nom_image,
                    'lang' => $lang,
                    'type_post' => $type_post,
                ]);
            } else {
                $alt_new = $titre;
            }
        }
        // Hook développeur
        $alt_new = apply_filters('autoalt_custom_alt', $alt_new, $src, $post->ID, [
            'method' => $method,
            'mot_cle' => $focus_keyword,
            'titre' => $titre,
            'nom_image' => $nom_image,
            'lang' => $lang,
            'type_post' => $type_post,
            'is_featured' => $is_featured,
        ]);
        // Préfixe/suffixe
        if ($prefix) $alt_new = $prefix . ' ' . $alt_new;
        if ($suffix) $alt_new = $alt_new . ' ' . $suffix;
        $alt_new = trim($alt_new);
        // Ciblage avancé selon le mode de remplacement ALT
        // Correction : si mode 'none', ne jamais injecter/modifier ALT/TITLE, même si vide
        if ($alt_replace_mode === 'none') {
            $do_replace = false;
            continue; // Ne rien faire pour cette image
        }
        $do_replace = false;
        if ($is_featured && !empty($featured_alt)) {
            $do_replace = true; // Toujours remplacer pour l'image à la une si champ manuel
        } else {
            switch ($alt_replace_mode) {
                case 'none':
                    $do_replace = false;
                    break;
                case 'all':
                    $do_replace = true;
                    break;
                case 'short':
                case 'short20':
                    $do_replace = (strlen(trim($alt)) < 20);
                    break;
                case 'empty':
                default:
                    $do_replace = (trim($alt) === '');
                    break;
            }
        }
        if ($do_replace) {
            $img->setAttribute('alt', esc_attr($alt_new));
            // Duplication dans title si activé
            if ($title_sync) {
                if (!$img->hasAttribute('title') || !$img->getAttribute('title')) {
                    $img->setAttribute('title', esc_attr($alt_new));
                }
            }
        }
    }
    // Sauvegarde du contenu modifié
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
