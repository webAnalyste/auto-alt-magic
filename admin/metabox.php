<?php
// Ajoute une metabox "Mot-clé principal" sur post, page, produit
add_action('add_meta_boxes', function() {
    $types = array('post', 'page', 'product');
    foreach ($types as $type) {
        add_meta_box('aam_focus_keyword', __('Mot-clé principal', 'auto-alt-magic'), 'aam_focus_keyword_metabox', $type, 'side');
    }
});

function aam_focus_keyword_metabox($post) {
    // Sécurité nonce
    wp_nonce_field('aam_focus_keyword_save', 'aam_focus_keyword_nonce');
    $value = get_post_meta($post->ID, 'aam_focus_keyword', true);
    echo '<input type="text" name="aam_focus_keyword" value="' . esc_attr($value) . '" style="width:100%" placeholder="Ex : tente randonnée" />';
    echo '<p class="description">Sera utilisé si aucun plugin SEO n’est détecté.</p>';
}

add_action('save_post', function($post_id) {
    // Vérification sécurité
    if (!isset($_POST['aam_focus_keyword_nonce']) || !wp_verify_nonce($_POST['aam_focus_keyword_nonce'], 'aam_focus_keyword_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    // Sauvegarde
    if (isset($_POST['aam_focus_keyword'])) {
        update_post_meta($post_id, 'aam_focus_keyword', sanitize_text_field($_POST['aam_focus_keyword']));
    }
}, 10, 1);
