<?php
// Ajoute la page de réglages Auto ALT Magic dans l’admin
add_action('admin_menu', function() {
    // Menu principal colonne gauche
    add_menu_page(
        __('Auto ALT Magic', 'auto-alt-magic'), // Titre menu
        __('Auto ALT Magic', 'auto-alt-magic'), // Label menu
        'manage_options',
        'auto-alt-magic-settings',
        'aam_settings_page_render',
        'dashicons-image-filter', // Icône WP
        56 // Position
    );
    // (Optionnel) Sous-menu dans Réglages
    add_options_page(
        __('Auto ALT Magic', 'auto-alt-magic'),
        __('Auto ALT Magic', 'auto-alt-magic'),
        'manage_options',
        'auto-alt-magic-settings',
        'aam_settings_page_render'
    );
});

// Enregistrement des options
add_action('admin_init', function() {
    register_setting('aam_settings_group', 'aam_method');
    register_setting('aam_settings_group', 'aam_text_libre');
    register_setting('aam_settings_group', 'aam_option_title_sync');
    
    
    register_setting('aam_settings_group', 'aam_prefix');
    register_setting('aam_settings_group', 'aam_suffix');

    // Sauvegarde sécurisée de la clé OpenAI
    add_action('pre_update_option_aam_openai_api_key', function($new, $old) {
        // Si champ vide ou masqué, ne pas modifier la clé existante
        if (empty($new) || $new === '************') {
            return $old;
        }
        // Ne jamais logger ni afficher la clé
        return sanitize_text_field($new);
    }, 10, 2);
    register_setting('aam_settings_group', 'aam_openai_api_key');
    // Enregistrement sécurisé des nouvelles options
    
    
    register_setting('aam_settings_group', 'aam_prefix');
    register_setting('aam_settings_group', 'aam_suffix');
});

function aam_settings_page_render() {
    // Liste dynamique des types de contenu publics
    $post_types = get_post_types(['public' => true], 'objects');
    // Récupération des réglages par type
    $all_settings = [];
    foreach ($post_types as $type => $obj) {
        $all_settings[$type] = get_option('aam_settings_' . $type, []);
    }
    // Liste des paramètres supportés
    $params = [
        'method' => ['label' => __('Méthode ALT', 'auto-alt-magic'), 'type' => 'select', 'tooltip' => __('Définit la façon dont l’attribut ALT sera généré pour chaque image. Effet immédiat sur la featured image et galeries dynamiques. Pour les images du contenu, la modification s’applique à la prochaine sauvegarde du post.', 'auto-alt-magic'), 'choices' => [
            'titre' => __('Titre de l’article', 'auto-alt-magic'),
            'nom_fichier' => __('Nom du fichier image', 'auto-alt-magic'),
            'titre_image' => __('Titre de l’image (média)', 'auto-alt-magic'),
            'texte_libre' => __('Texte libre (balises dynamiques)', 'auto-alt-magic'),
        ]],
        'text_libre' => ['label' => __('Texte libre personnalisé', 'auto-alt-magic'), 'type' => 'text', 'tooltip' => __('Utilisez des balises dynamiques comme {{mot_cle}}, {{titre}}, etc. S’applique au prochain enregistrement du post.', 'auto-alt-magic')],
        'option_title_sync' => ['label' => __('Copier ALT vers TITLE si vide', 'auto-alt-magic'), 'type' => 'checkbox', 'tooltip' => __('Si TITLE est absent, il sera automatiquement dupliqué à partir de l’ALT.', 'auto-alt-magic')],
        'alt_replace_mode' => [
            'label' => __('Règle de remplacement ALT', 'auto-alt-magic'),
            'type' => 'select',
            'tooltip' => __('Détermine quand le plugin remplace l’ALT existant. Effet immédiat sur la featured image et galeries dynamiques. Pour les images intégrés dans les contenus, nécessite une sauvegarde du post ou traitement par lot (payant).', 'auto-alt-magic'),
            'choices' => [
                'none' => __('Ne pas remplacer', 'auto-alt-magic'),
                'empty' => __('Remplacer si ALT vide', 'auto-alt-magic'),
                'all' => __('Toujours remplacer', 'auto-alt-magic'),
                'short20' => __('Remplacer si ALT < 20 caractères', 'auto-alt-magic'),
            ]
        ],
        'prefix' => ['label' => __('Préfixe ALT', 'auto-alt-magic'), 'type' => 'text', 'tooltip' => __('Ajoute un texte avant l’ALT généré.', 'auto-alt-magic')],
        'suffix' => ['label' => __('Suffixe ALT', 'auto-alt-magic'), 'type' => 'text', 'tooltip' => __('Ajoute un texte après l’ALT généré.', 'auto-alt-magic')],
    ];
    // Gestion de la soumission du formulaire
    if (isset($_POST['aam_save_settings']) && check_admin_referer('aam_settings_save', 'aam_settings_nonce')) {
        foreach ($post_types as $type => $obj) {
            $save = [];
            foreach ($params as $key => $def) {
                if ($def['type'] === 'checkbox') {
                    $save[$key] = isset($_POST['aam'][$type][$key]) ? 1 : 0;
                } else {
                    $save[$key] = isset($_POST['aam'][$type][$key]) ? sanitize_text_field($_POST['aam'][$type][$key]) : '';
                }
            }
            update_option('aam_settings_' . $type, $save);
            $all_settings[$type] = $save;
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Réglages enregistrés.', 'auto-alt-magic') . '</p></div>';
    }
    ?>
    <div class="wrap aam-settings-tabs" style="padding-left:48px;">

        <h1><?php _e('Réglages Auto ALT Magic', 'auto-alt-magic'); ?></h1>
        <form method="post">
            <?php wp_nonce_field('aam_settings_save', 'aam_settings_nonce'); ?>
            <h2 class="nav-tab-wrapper">
                <?php $first = true; foreach ($post_types as $type => $obj): ?>
                    <a href="#aam-tab-<?php echo esc_attr($type); ?>" class="nav-tab<?php if ($first) echo ' nav-tab-active'; ?>" onclick="event.preventDefault(); aamShowTab('<?php echo esc_attr($type); ?>');"><?php echo esc_html($obj->labels->singular_name); ?></a>
                <?php $first = false; endforeach; ?>
            </h2>
            <?php $first = true; foreach ($post_types as $type => $obj): ?>
                <div id="aam-tab-<?php echo esc_attr($type); ?>" class="aam-tab-content" style="<?php if (!$first) echo 'display:none;'; ?>margin-top:20px;">
                    <table class="form-table aam-form-table-premium">
                        <?php foreach ($params as $key => $def): ?>
                        <tr valign="top"
                            <?php
                                $tr_class = '';
                                $tr_style = '';
                                if ($key === 'text_libre') {
                                    $tr_class .= ' aam-row-text-libre aam-row-text-libre-' . esc_attr($type);
                                    if (($all_settings[$type]['method'] ?? '') !== 'texte_libre') {
                                        $tr_style = 'display:none;';
                                    }
                                }
                                if ($key === 'option_title_sync') {
                                    $tr_class .= ' aam-row-title-sync aam-row-title-sync-' . esc_attr($type);
                                    if (($all_settings[$type]['method'] ?? '') === 'titre_image') {
                                        $tr_style = 'display:none;';
                                    }
                                }
                                if ($tr_class) echo ' class="' . trim($tr_class) . '"';
                                if ($tr_style) echo ' style="' . $tr_style . '"';
                            ?>
                        >
                            <th scope="row">
                                <?php echo esc_html($def['label']); ?>
                                <?php if (!empty($def['tooltip'])): ?>
                                    <span class="aam-tooltip-icon" tabindex="0" aria-label="<?php echo esc_attr(strip_tags($def['tooltip'])); ?>">&#9432;
                                        <span class="aam-tooltip-text"><?php echo esc_html($def['tooltip']); ?></span>
                                    </span>
                                <?php endif; ?>
                            </th>
    </th>
                            <td>
                                <?php if ($def['type'] === 'select'): ?>
    <select name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" class="aam-method-select" data-type="<?php echo esc_attr($type); ?>" id="aam-alt-replace-mode-<?php echo esc_attr($type); ?>-<?php echo esc_attr($key); ?>">
        <?php foreach ($def['choices'] as $val => $lab): ?>
            <option value="<?php echo esc_attr($val); ?>" <?php selected(($all_settings[$type][$key] ?? '') == $val); ?>><?php echo esc_html($lab); ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($key === 'alt_replace_mode'): ?>
        <div class="aam-alt-replace-none-msg" style="display:<?php echo (($all_settings[$type]['alt_replace_mode'] ?? '') === 'none') ? 'block' : 'none'; ?>;margin-top:4px;font-size:11px;color:#666;">
            <?php _e('Restaure les ALT d’origine des images à la une et bloque les modifs des images intégrées.<br>Pour restaurer les ALT : « Reset » dans chaque post ou traitement par lot (Pro).', 'auto-alt-magic'); ?>
        </div>
        <script>document.addEventListener('DOMContentLoaded',function(){
            var sel=document.getElementById('aam-alt-replace-mode-<?php echo esc_attr($type); ?>-<?php echo esc_attr($key); ?>');
            var msg=sel.parentNode.querySelector('.aam-alt-replace-none-msg');
            if(sel&&msg){
                sel.addEventListener('change',function(){
                    msg.style.display=(sel.value==='none')?'block':'none';
                });
            }
        });</script>
    <?php endif; ?>
<?php elseif ($def['type'] === 'checkbox'): ?>
    <input type="checkbox" name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" value="1" <?php checked(!empty($all_settings[$type][$key])); ?> class="aam-title-sync-checkbox aam-title-sync-checkbox-<?php echo esc_attr($type); ?>" data-type="<?php echo esc_attr($type); ?>" style="<?php echo ($all_settings[$type]['method'] ?? '') === 'titre_image' && $key === 'option_title_sync' ? 'display:none;' : '' ?>" />
<?php else: ?>
    <div class="aam-text-libre-wrap aam-text-libre-<?php echo esc_attr($type); ?>">
        <input type="text" name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($all_settings[$type][$key] ?? ''); ?>" style="width:200px;max-width:100%;" />
        <br><small><?php _e('Balises supportées', 'auto-alt-magic'); ?> : {{mot_cle}}, {{titre}}, {{nom_image}}, {{lang}}, {{type_post}}</small>
    </div>
<?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php $first = false; endforeach; ?>
            <p style="margin-top:32px;text-align:right;">
    <button type="submit" class="button button-primary" name="aam_save_settings" style="font-size:16px;padding:10px 24px 10px 18px;display:inline-flex;align-items:center;gap:8px;background:#2271b1;border:none;">
        <span class="dashicons dashicons-yes-alt" style="font-size:18px;vertical-align:middle;"></span>
        <?php esc_html_e('Enregistrer les réglages', 'auto-alt-magic'); ?>
    </button>
</p>
        </form>
    </div>
    <script>
    function aamShowTab(type) {
        document.querySelectorAll('.aam-tab-content').forEach(function(tab){tab.style.display='none';});
        document.querySelectorAll('.nav-tab').forEach(function(tab){tab.classList.remove('nav-tab-active');});
        document.getElementById('aam-tab-'+type).style.display='block';
        document.querySelector('a[href="#aam-tab-'+type+'"]').classList.add('nav-tab-active');
    }
    </script>
    <style>
    .aam-settings-tabs .nav-tab-wrapper {margin-bottom:0;}
    .aam-tab-content {background:#fff; border:1px solid #ccd0d4; border-top:none; padding:20px;}
    .aam-tooltip-icon {
        display:inline-block;
        margin-left:8px;
        color:#2271b1;
        background:#eaf6fb;
        border-radius:50%;
        width:18px;
        height:18px;
        text-align:center;
        font-size:15px;
        line-height:18px;
        cursor:pointer;
        position:relative;
        vertical-align:middle;
        transition:background 0.2s;
    }
    .aam-tooltip-icon:focus, .aam-tooltip-icon:hover {
        background:#d0e7f4;
        outline:none;
    }
    .aam-tooltip-text {
        visibility:hidden;
        opacity:0;
        width:260px;
        background:#222;
        color:#fff;
        text-align:left;
        border-radius:6px;
        padding:8px 12px;
        position:absolute;
        z-index:99;
        bottom:125%;
        left:50%;
        margin-left:20px;
        font-size:13px;
        box-shadow:0 4px 14px rgba(0,0,0,0.14);
        transition:opacity 0.2s;
        pointer-events:none;
    }
    .aam-tooltip-icon:hover .aam-tooltip-text, .aam-tooltip-icon:focus .aam-tooltip-text {
        visibility:visible;
        opacity:1;
        pointer-events:auto;
    }
</style>
    <script>
    jQuery(document).ready(function($) {
        // Affichage conditionnel sur changement de méthode
        $(document).on('change', '.aam-method-select', function() {
            var method = $(this).val();
            var type = $(this).data('type');
            
            // Champ Texte libre
            var textLibreRow = $('tr.aam-row-text-libre-' + type);
            if (method === 'texte_libre') {
                textLibreRow.show();
            } else {
                textLibreRow.hide();
            }
            
            // Option ALT->TITLE
            var titleSyncRow = $('tr.aam-row-title-sync-' + type);
            if (method === 'titre_image') {
                titleSyncRow.hide();
                titleSyncRow.find('input[type="checkbox"]').prop('checked', false);
            } else {
                titleSyncRow.show();
            }
        });
    });
    </script>
    <?php
}
