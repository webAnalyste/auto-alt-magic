<?php
// Solution ultime : injection ALT/TITLE via JS minimal en wp_head (uniquement si aucune solution PHP native ne fonctionne)
add_action('wp_head', function() {
    if (!is_single() && !is_page()) return;
    global $post;
    if (!$post) return;
    
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
    var imgs = document.querySelectorAll('img');
    for (var i = 0; i < imgs.length; i++) {
        var img = imgs[i];
        if (img.src && (img.src.indexOf('" . esc_js($thumb_filename) . "') !== -1 || img.srcset && img.srcset.indexOf('" . esc_js($thumb_filename) . "') !== -1)) {
            img.alt = '" . $alt_escaped . "';
            if (!img.title) img.title = '" . $alt_escaped . "';
        }
    }
});
</script>";
}, 999);
