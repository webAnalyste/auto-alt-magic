<?php
// Solution ultime : injection ALT/TITLE via JS minimal en wp_head (uniquement si aucune solution PHP native ne fonctionne)
add_action('wp_head', function() {
    // DÉSACTIVATION TOTALE sur archives pour éviter tout plantage
    if (is_archive() || is_category() || is_tag() || is_tax()) return;
    if (!is_single() && !is_page() && !is_product()) return;
    global $post;
    if (!is_object($post) || !($post instanceof WP_Post) || !isset($post->ID)) {
        if (defined('WP_DEBUG') && WP_DEBUG) error_log('[AAM] Contexte post anormal dans wp_head JS (archive ?): ' . print_r($post, true));
        return;
    }
    
    // Test de base : vérifier que le hook wp_head fonctionne
    echo "<!-- AAM: Hook wp_head actif pour post ID: {$post->ID} -->\n";
    
    // Désactivation JS si reset natif OU mode 'ne rien remplacer' (par type de contenu)
    $type = $post->post_type;
    $type_settings = get_option('aam_settings_' . $type, []);
    $alt_replace_mode = isset($type_settings['alt_replace_mode']) ? $type_settings['alt_replace_mode'] : 'empty';
    $is_reset = get_post_meta($post->ID, 'aam_reset_native_alt', true);
    if ($alt_replace_mode === 'none' || $is_reset === '1') {
        return;
    }
    
    // ALT manuel (metabox)
    $manual_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    // ALT global (fallback)
    if (empty($manual_alt) && function_exists('aam_get_default_featured_alt')) {
        $manual_alt = aam_get_default_featured_alt($post);
    }
    if (empty($manual_alt)) {
        $manual_alt = get_the_title($post->ID);
    }
    
    $thumb_id = get_post_thumbnail_id($post->ID);
    if (!$thumb_id) return;
    
    $thumb_url = wp_get_attachment_url($thumb_id);
    if (!$thumb_url) return;
    
    $alt_escaped = esc_js($manual_alt);
    $thumb_filename = basename($thumb_url);
    
    echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('AAM: Recherche featured image ID: " . $thumb_id . "');
    var altText = '" . $alt_escaped . "';
    var found = false;
    
    // Méthode 1: par attachment-id (data-attachment-id)
    var imgById = document.querySelector('img[data-attachment-id=\"" . $thumb_id . "\"]');
    if (imgById) {
        imgById.alt = altText;
        if (!imgById.title) imgById.title = altText;
        console.log('AAM: ALT injecté via attachment-id');
        found = true;
    }
    
    // Méthode 2: par classe wp-post-image
    var imgByClass = document.querySelector('img.wp-post-image');
    if (imgByClass && !found) {
        imgByClass.alt = altText;
        if (!imgByClass.title) imgByClass.title = altText;
        console.log('AAM: ALT injecté via wp-post-image - Valeur: ' + imgByClass.alt);
        // Vérification immédiate
        setTimeout(function() {
            console.log('AAM: Vérification ALT après 100ms: ' + imgByClass.alt);
            if (!imgByClass.alt || imgByClass.alt === '') {
                imgByClass.alt = altText;
                console.log('AAM: ALT réinjecté après écrasement WooCommerce');
            }
        }, 100);
        found = true;
    }
    
    // Méthode 3: par nom de fichier (fallback)
    if (!found) {
        var imgs = document.querySelectorAll('img');
        for (var i = 0; i < imgs.length; i++) {
            var img = imgs[i];
            if (img.src && (img.src.indexOf('" . esc_js($thumb_filename) . "') !== -1 || (img.srcset && img.srcset.indexOf('" . esc_js($thumb_filename) . "') !== -1))) {
                img.alt = altText;
                if (!img.title) img.title = altText;
                console.log('AAM: ALT injecté via filename: " . esc_js($thumb_filename) . "');
                found = true;
                break;
            }
        }
    }
    
    if (!found) {
        console.log('AAM: Aucune featured image trouvée pour injection ALT');
    }
    
    // Injection ALT sur galerie WooCommerce (images supplémentaires)
    var wooGalleryImgs = document.querySelectorAll('.woocommerce-product-gallery__image img, .flex-viewport img, .woocommerce-product-gallery img');
    wooGalleryImgs.forEach(function(img) {
        if (img.src && (img.src.indexOf('" . esc_js($thumb_filename) . "') !== -1 || img.getAttribute('data-attachment-id') === '" . $thumb_id . "')) {
            img.alt = altText;
            if (!img.title) img.title = altText;
            console.log('AAM: ALT injecté sur galerie WooCommerce');
        }
    });
    
    // MutationObserver pour réinjecter ALT si WooCommerce modifie l'image
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || mutation.type === 'attributes') {
                var imgs = document.querySelectorAll('img.wp-post-image, img[data-attachment-id=\"" . $thumb_id . "\"], .woocommerce-product-gallery__image img, .flex-viewport img, .woocommerce-product-gallery img');
                imgs.forEach(function(img) {
                    if ((!img.alt || img.alt === '') && (img.src.indexOf('" . esc_js($thumb_filename) . "') !== -1 || img.getAttribute('data-attachment-id') === '" . $thumb_id . "')) {
                        img.alt = altText;
                        if (!img.title) img.title = altText;
                        console.log('AAM: ALT réinjecté après modification DOM (galerie)');
                    }
                });
            }
        });
    });
    observer.observe(document.body, { childList: true, subtree: true, attributes: true, attributeFilter: ['alt', 'src'] });
});
</script>";
}, 999);
