<?php
// Ajoute une metabox unique "Auto ALT Magic" sur post, page, produit, CPT
add_action('add_meta_boxes', function() {
    $types = get_post_types(['public' => true], 'names');
    foreach ($types as $type) {
        add_meta_box('aam_magic_box', __('Auto ALT Magic', 'auto-alt-magic'), 'aam_magic_metabox', $type, 'side');
    }
});

function aam_magic_metabox($post) {
    // Sécurité nonce
    wp_nonce_field('aam_magic_box_save', 'aam_magic_box_nonce');
    // Valeurs actuelles
    $focus_keyword = get_post_meta($post->ID, 'aam_focus_keyword', true);
    $featured_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    // Options globales (fallback)
    $method = get_post_meta($post->ID, 'aam_method', true) ?: get_option('aam_method', 'titre');
    $text_libre = get_post_meta($post->ID, 'aam_text_libre', true) ?: get_option('aam_text_libre', '');
    $prefix = get_post_meta($post->ID, 'aam_prefix', true) ?: get_option('aam_prefix', '');
    $suffix = get_post_meta($post->ID, 'aam_suffix', true) ?: get_option('aam_suffix', '');
    $only_empty = get_post_meta($post->ID, 'aam_only_empty_alt', true) ?: get_option('aam_only_empty_alt', 0);
    $replace_all = get_post_meta($post->ID, 'aam_replace_all_alt', true) ?: get_option('aam_replace_all_alt', 0);
    $title_sync = get_post_meta($post->ID, 'aam_option_title_sync', true) ?: get_option('aam_option_title_sync', 1);
    // Affichage UI
    echo '<p><strong>' . __('Mot-clé principal', 'auto-alt-magic') . '</strong><br />';
    echo '<input type="text" name="aam_focus_keyword" value="' . esc_attr($focus_keyword) . '" style="width:100%" placeholder="Ex : tente randonnée" /></p>';
    echo '<p><strong>' . __('ALT manuel de l’image à la une', 'auto-alt-magic') . '</strong><br />';
    echo '<input type="text" name="aam_featured_alt" value="' . esc_attr($featured_alt) . '" style="width:100%" placeholder="ALT personnalisé pour cette image à la une" /></p>';
    echo '<hr />';
    echo '<p><strong>' . __('Paramètres contextuels', 'auto-alt-magic') . '</strong></p>';
    // Méthode
    echo '<label>' . __('Méthode de génération ALT :', 'auto-alt-magic') . '</label><br />';
    echo '<select name="aam_method">
        <option value="titre"' . selected($method, 'titre', false) . '>Titre du post</option>
        <option value="nom_fichier"' . selected($method, 'nom_fichier', false) . '>Nom du fichier image</option>
        <option value="texte_libre"' . selected($method, 'texte_libre', false) . '>Texte libre personnalisé</option>
    </select><br />';
    // Texte libre
    echo '<textarea name="aam_text_libre" style="width:100%;min-height:40px;" placeholder="Ex : Photo de {{titre}} - {{mot_cle}}">' . esc_textarea($text_libre) . '</textarea>';
    // Préfixe/suffixe
    echo '<input type="text" name="aam_prefix" value="' . esc_attr($prefix) . '" placeholder="Préfixe ALT" style="width:49%" /> ';
    echo '<input type="text" name="aam_suffix" value="' . esc_attr($suffix) . '" placeholder="Suffixe ALT" style="width:49%" />';
    // Ciblage
    echo '<p><label><input type="checkbox" name="aam_only_empty_alt" value="1"' . checked($only_empty, 1, false) . ' /> ' . __('Traiter uniquement les images sans alt', 'auto-alt-magic') . '</label><br />';
    echo '<label><input type="checkbox" name="aam_replace_all_alt" value="1"' . checked($replace_all, 1, false) . ' /> ' . __('Traiter toutes les images (remplacer alt existant)', 'auto-alt-magic') . '</label></p>';
    // Duplication ALT->TITLE
    echo '<p><label><input type="checkbox" name="aam_option_title_sync" value="1"' . checked($title_sync, 1, false) . ' /> ' . __('Copier automatiquement le alt dans title si title absent', 'auto-alt-magic') . '</label></p>';
    echo '<p class="description">' . __('Toutes ces options sont contextuelles à ce contenu. Elles surchargent les réglages globaux pour ce post/page/produit.', 'auto-alt-magic') . '</p>';
}

add_action('save_post', function($post_id) {
    // Sécurité nonce et droits
    if (!isset($_POST['aam_magic_box_nonce']) || !wp_verify_nonce($_POST['aam_magic_box_nonce'], 'aam_magic_box_save')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    // Sauvegarde des paramètres contextuels
    $fields = [
        'aam_focus_keyword',
        'aam_featured_alt',
        'aam_method',
        'aam_text_libre',
        'aam_prefix',
        'aam_suffix',
        'aam_only_empty_alt',
        'aam_replace_all_alt',
        'aam_option_title_sync'
    ];
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = is_string($_POST[$field]) ? sanitize_text_field($_POST[$field]) : intval($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        } else {
            // Si case décochée, on supprime la meta pour fallback sur l’option globale
            delete_post_meta($post_id, $field);
        }
    }
}, 10, 1);
