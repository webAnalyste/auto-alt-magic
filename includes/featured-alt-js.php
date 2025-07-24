<?php
// Solution ultime : injection ALT/TITLE via JS minimal en wp_head (uniquement si aucune solution PHP native ne fonctionne)
add_action('wp_head', function() {
    if (!is_single() && !is_page()) return;
    global $post;
    if (!$post) return;
    
    // Test de base : vérifier que le hook wp_head fonctionne
    echo "<!-- AAM: Hook wp_head actif pour post ID: {$post->ID} -->\n";
    
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
        console.log('AAM: ALT injecté via wp-post-image');
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
});
</script>";
}, 999);
