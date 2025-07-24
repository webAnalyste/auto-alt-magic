<?php
// Ajoute une metabox unique "Auto ALT Magic" sur post, page, produit, CPT
add_action('add_meta_boxes', function() {
    $types = get_post_types(['public' => true], 'names');
    foreach ($types as $type) {
        add_meta_box('aam_magic_box', __('Auto ALT Magic', 'auto-alt-magic'), 'aam_magic_metabox', $type, 'side');
    }
});

require_once AAM_PLUGIN_DIR . 'includes/aam_get_default_featured_alt.php';
function aam_magic_metabox($post) {
    // Sécurité nonce
    wp_nonce_field('aam_magic_box_save', 'aam_magic_box_nonce');
    // Valeur actuelle du ALT manuel (champ unique)
    $featured_alt = get_post_meta($post->ID, 'aam_featured_alt', true);
    // Valeur par défaut (template global du post type)
    $default_alt = aam_get_default_featured_alt($post);
    // Si pas de valeur manuelle, pré-remplir avec la valeur globale
    if (empty($featured_alt)) {
        $featured_alt = $default_alt;
    }
    echo '<p><strong>' . __('ALT de l’image à la une', 'auto-alt-magic') . '</strong><br />';
    echo '<input type="text" name="aam_featured_alt" value="' . esc_attr($featured_alt) . '" style="width:100%" placeholder="ALT généré automatiquement selon le type de contenu" readonly class="aam-featured-alt-input" />';
    echo '<br /><label><input type="checkbox" class="aam-edit-featured-alt" /> ' . __('Éditer le champ ALT manuellement', 'auto-alt-magic') . '</label></p>';
    // Affichage valeur globale si personnalisé
    if (!empty($featured_alt) && $featured_alt !== $default_alt) {
        echo '<div class="aam-default-alt-info" style="margin-bottom:6px;color:#888;font-size:12px"><span style="font-weight:bold">' . __('Valeur globale par défaut :', 'auto-alt-magic') . '</span> <span class="aam-default-alt-txt">' . esc_html($default_alt) . '</span></div>';
    } else {
        echo '<div class="aam-default-alt-info" style="margin-bottom:6px;color:#888;font-size:12px;display:none"><span style="font-weight:bold">' . __('Valeur globale par défaut :', 'auto-alt-magic') . '</span> <span class="aam-default-alt-txt">' . esc_html($default_alt) . '</span></div>';
    }
    // Affichage valeur personnalisée précédente (après reset)
    echo '<div class="aam-previous-alt-info" style="margin-bottom:6px;color:#888;font-size:12px;display:none"><span style="font-weight:bold">' . __('Ancienne valeur personnalisée :', 'auto-alt-magic') . '</span> <span class="aam-previous-alt-txt"></span></div>';
    echo '<p class="description">' . __('Ce champ est généré automatiquement selon les réglages globaux du type de contenu, mais peut être écrasé ici par un texte libre (prioritaire).', 'auto-alt-magic') . '</p>';

    // Bouton reset ALT manuel
    echo '<button type="button" class="button aam-reset-featured-alt" data-default-alt="' . esc_attr($default_alt) . '">' . __('Réinitialiser avec la valeur globale', 'auto-alt-magic') . '</button>';

// Injection JS robuste UX
?>
<script>
document.addEventListener("DOMContentLoaded",function(){
  document.querySelectorAll(".aam-reset-featured-alt").forEach(function(btn){
    btn.addEventListener("click",function(){
      var box = btn.closest('.postbox');
      var input = box.querySelector('input[name="aam_featured_alt"]');
      var prevAltInfo = box.querySelector('.aam-previous-alt-info');
      var prevAltTxt = box.querySelector('.aam-previous-alt-txt');
      if(input) {
        var previous = input.value;
        input.value = btn.getAttribute("data-default-alt");
        if(previous && previous !== btn.getAttribute("data-default-alt")) {
          prevAltTxt.textContent = previous;
          prevAltInfo.style.display = '';
        } else {
          prevAltInfo.style.display = 'none';
        }
      }
    });
  });
  document.querySelectorAll(".aam-edit-featured-alt").forEach(function(checkbox){
    var input = checkbox.closest('p').querySelector('input[name="aam_featured_alt"]');
    var box = checkbox.closest('.postbox');
    var defaultAltInfo = box.querySelector('.aam-default-alt-info');
    checkbox.addEventListener("change",function(){
      if(checkbox.checked){
        input.removeAttribute('readonly');
        input.focus();
        if(defaultAltInfo) defaultAltInfo.style.display = '';
      }else{
        input.setAttribute('readonly','readonly');
        input.value = box.querySelector('.aam-reset-featured-alt').getAttribute('data-default-alt');
        if(defaultAltInfo) defaultAltInfo.style.display = 'none';
      }
    });
  });
});
</script>
<?php


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
        'aam_option_title_sync',
        'aam_alt_replace_mode'
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
