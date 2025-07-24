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
        'method' => ['label' => __('Méthode de génération ALT', 'auto-alt-magic'), 'type' => 'select', 'choices' => [
            'titre' => __('Titre du post', 'auto-alt-magic'),
            'nom_fichier' => __('Nom du fichier image', 'auto-alt-magic'),
            'titre_image' => __('Titre de l\'image (media title)', 'auto-alt-magic'),
            'texte_libre' => __('Texte libre avec balises dynamiques', 'auto-alt-magic'),
        ]],
        'text_libre' => ['label' => __('Texte libre', 'auto-alt-magic'), 'type' => 'text'],
        'option_title_sync' => ['label' => __('Dupliquer ALT vers TITLE si manquant', 'auto-alt-magic'), 'type' => 'checkbox'],
        'alt_replace_mode' => [
            'label' => __('Remplacement des attributs ALT', 'auto-alt-magic'),
            'type' => 'select',
            'choices' => [
                'none' => __('Ne rien remplacer', 'auto-alt-magic'),
                'empty' => __('Remplacer si ALT vide', 'auto-alt-magic'),
                'all' => __('Remplacer tous les ALT', 'auto-alt-magic'),
                'short' => __('Remplacer si ALT < 30 caractères', 'auto-alt-magic'),
            ]
        ],
        'prefix' => ['label' => __('Préfixe ALT', 'auto-alt-magic'), 'type' => 'text'],
        'suffix' => ['label' => __('Suffixe ALT', 'auto-alt-magic'), 'type' => 'text'],
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
    <div class="wrap aam-settings-tabs">
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
                    <h2><?php echo esc_html($obj->labels->singular_name); ?></h2>
                    <table class="form-table">
                        <?php foreach ($params as $key => $def): ?>
                        <tr valign="top"<?php if ($key === 'text_libre') { echo ' class="aam-row-text-libre aam-row-text-libre-' . esc_attr($type) . '"'; } ?>>
                            <th scope="row"><?php echo esc_html($def['label']); ?></th>
                            <td>
                                <?php if ($def['type'] === 'select'): ?>
    <select name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" class="aam-method-select" data-type="<?php echo esc_attr($type); ?>">
        <?php foreach ($def['choices'] as $val => $lab): ?>
            <option value="<?php echo esc_attr($val); ?>" <?php selected(($all_settings[$type][$key] ?? '') == $val); ?>><?php echo esc_html($lab); ?></option>
        <?php endforeach; ?>
    </select>
<?php elseif ($def['type'] === 'checkbox'): ?>
    <input type="checkbox" name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" value="1" <?php checked(!empty($all_settings[$type][$key])); ?> class="aam-title-sync-checkbox aam-title-sync-checkbox-<?php echo esc_attr($type); ?>" data-type="<?php echo esc_attr($type); ?>" style="<?php echo ($all_settings[$type]['method'] ?? '') === 'titre_image' && $key === 'option_title_sync' ? 'display:none;' : '' ?>" />
<?php else: ?>
    <div class="aam-text-libre-wrap aam-text-libre-<?php echo esc_attr($type); ?>">
        <input type="text" name="aam[<?php echo esc_attr($type); ?>][<?php echo esc_attr($key); ?>]" value="<?php echo esc_attr($all_settings[$type][$key] ?? ''); ?>" style="width:100%" />
        <br><small><?php _e('Balises supportées', 'auto-alt-magic'); ?> : {{mot_cle}}, {{titre}}, {{nom_image}}, {{lang}}, {{type_post}}</small>
    </div>
<?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php $first = false; endforeach; ?>
            <p><input type="submit" class="button-primary" name="aam_save_settings" value="<?php esc_attr_e('Enregistrer les réglages', 'auto-alt-magic'); ?>" /></p>
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
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
    // Initial hide/show on load
    document.querySelectorAll('.aam-method-select').forEach(function(sel) {
        var type = sel.getAttribute('data-type');
        var show = sel.value === 'texte_libre';
        var row = document.querySelector('.aam-row-text-libre-' + type);
        if (row) row.style.display = show ? 'table-row' : 'none';
        // Hide/show ALT->TITLE option if needed
        var titleSyncCheckbox = document.querySelector('.aam-title-sync-checkbox-' + type);
        if (titleSyncCheckbox && titleSyncCheckbox.name.includes('option_title_sync')) {
            titleSyncCheckbox.style.display = (sel.value === 'titre_image') ? 'none' : '';
            if (sel.value === 'titre_image') titleSyncCheckbox.checked = false;
        }
    });
    // On select change
    document.querySelectorAll('.aam-method-select').forEach(function(sel) {
        sel.addEventListener('change', function(e) {
            var type = this.getAttribute('data-type');
            var show = this.value === 'texte_libre';
            var row = document.querySelector('.aam-row-text-libre-' + type);
            if (row) row.style.display = show ? 'table-row' : 'none';
            // Hide/show ALT->TITLE option if needed
            var titleSyncCheckbox = document.querySelector('.aam-title-sync-checkbox-' + type);
            if (titleSyncCheckbox && titleSyncCheckbox.name.includes('option_title_sync')) {
                titleSyncCheckbox.style.display = (this.value === 'titre_image') ? 'none' : '';
                if (this.value === 'titre_image') titleSyncCheckbox.checked = false;
            }
        });
    });
});        });
    });
    </script>
    <?php
}
