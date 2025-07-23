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
    register_setting('aam_settings_group', 'aam_only_empty_alt');
    register_setting('aam_settings_group', 'aam_replace_all_alt');
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
    register_setting('aam_settings_group', 'aam_only_empty_alt');
    register_setting('aam_settings_group', 'aam_replace_all_alt');
    register_setting('aam_settings_group', 'aam_prefix');
    register_setting('aam_settings_group', 'aam_suffix');
});

function aam_settings_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('Réglages Auto ALT Magic', 'auto-alt-magic'); ?></h1>
        <form method="post" action="options.php">
            <?php settings_fields('aam_settings_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Méthode de génération ALT</th>
                    <td>
                        <select name="aam_method">
                            <option value="titre" <?php selected(get_option('aam_method'), 'titre'); ?>><?php _e('Titre du post', 'auto-alt-magic'); ?></option>
                            <option value="nom_fichier" <?php selected(get_option('aam_method'), 'nom_fichier'); ?>><?php _e('Nom du fichier image', 'auto-alt-magic'); ?></option>
                            <option value="texte_libre" <?php selected(get_option('aam_method'), 'texte_libre'); ?>><?php _e('Texte libre (avec balises)', 'auto-alt-magic'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Texte libre (si sélectionné)</th>
                    <td>
                        <input type="text" name="aam_text_libre" value="<?php echo esc_attr(get_option('aam_text_libre', '')); ?>" style="width: 100%" placeholder="Ex : Image de {{type_post}} : {{titre}} (mot-clé : {{mot_cle}})" />
                        <p class="description">Balises supportées : {{mot_cle}}, {{titre}}, {{nom_image}}, {{lang}}, {{type_post}}</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Dupliquer ALT vers TITLE si manquant</th>
                    <td>
                        <input type="checkbox" name="aam_option_title_sync" value="1" <?php checked(get_option('aam_option_title_sync'), 1); ?> />
                        <span class="description">Ajoute automatiquement un attribut title identique à alt si title est absent.</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Clé API OpenAI (Pro)</th>
                    <td>
                        <?php $val = !empty(get_option('aam_openai_api_key')) ? '************' : ''; ?>
                        <input type="password" name="aam_openai_api_key" value="<?php echo esc_attr($val); ?>" autocomplete="off" placeholder="sk-..." />
                        <small>Jamais stockée ni affichée en clair. Usage backend uniquement.</small>
                    </td>
                </tr>
            <!-- Options de ciblage -->
            <tr valign="top">
                <th scope="row">Ciblage des images</th>
                <td>
                    <label><input type="checkbox" name="aam_only_empty_alt" value="1" <?php checked(get_option('aam_only_empty_alt'), 1); ?> /> Traiter uniquement les images sans alt</label><br />
                    <label><input type="checkbox" name="aam_replace_all_alt" value="1" <?php checked(get_option('aam_replace_all_alt'), 1); ?> /> Traiter toutes les images (remplacer alt existant)</label>
                </td>
            </tr>
            <!-- Préfixe/Suffixe -->
            <tr valign="top">
                <th scope="row">Préfixe ALT</th>
                <td><input type="text" name="aam_prefix" value="<?php echo esc_attr(get_option('aam_prefix', '')); ?>" placeholder="Ex : Photo de " style="width: 200px;" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Suffixe ALT</th>
                <td><input type="text" name="aam_suffix" value="<?php echo esc_attr(get_option('aam_suffix', '')); ?>" placeholder="Ex : - boutique" style="width: 200px;" /></td>
            </tr>
            <!-- Duplication ALT->TITLE -->
            <tr valign="top">
                <th scope="row">Attribut title</th>
                <td><label><input type="checkbox" name="aam_option_title_sync" value="1" <?php checked(get_option('aam_option_title_sync'), 1); ?> /> Copier automatiquement le alt dans title si title absent</label></td>
            </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
