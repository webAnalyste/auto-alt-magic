<?php
/*
Plugin Name: Auto ALT Magic
Description: Génération et injection automatique des attributs ALT et TITLE dans les balises <img> des contenus WordPress (posts, pages, produits, etc.).
Version: 1.0.2
Author: webAnalyste / Cascade
Text Domain: auto-alt-magic
Domain Path: /languages
*/

// Sécurité : blocage accès direct
if (!defined('ABSPATH')) exit;

// Définition des constantes de base
if (!defined('AAM_PLUGIN_DIR'))
    define('AAM_PLUGIN_DIR', plugin_dir_path(__FILE__));
if (!defined('AAM_PLUGIN_URL'))
    define('AAM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Chargement du cœur du plugin
require_once AAM_PLUGIN_DIR . 'includes/seo.php';
require_once AAM_PLUGIN_DIR . 'includes/core.php';
require_once AAM_PLUGIN_DIR . 'includes/plans.php';
require_once AAM_PLUGIN_DIR . 'includes/settings.php';

// Hooks d'activation/désactivation
register_activation_hook(__FILE__, 'aam_activate');
register_deactivation_hook(__FILE__, 'aam_deactivate');

function aam_activate() {
    // Sécurité : rien de destructif, préparation éventuelle des options
    // (Exemple : add_option('aam_version', '1.0.0'));
}

function aam_deactivate() {
    // Sécurité : pas de suppression de données critiques
    // (Exemple : flush_rewrite_rules())
}

// Hook sur la sauvegarde de post (posts, pages, produits)
add_action('save_post', 'aam_process_post_content', 20, 3);
function aam_process_post_content($post_ID, $post, $update) {
    // Vérification des droits et du contexte
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_ID)) return;
    if ($post->post_type !== 'post' && $post->post_type !== 'page' && $post->post_type !== 'product') return;
    // Traitement du contenu (core.php)
    aam_core_process_post($post_ID, $post);
}

// Chargement des filtres ALT/TITLE côté front ET admin
require_once AAM_PLUGIN_DIR . 'includes/featured-alt-filter.php';
require_once AAM_PLUGIN_DIR . 'includes/alt-attributes-filter.php';
require_once AAM_PLUGIN_DIR . 'includes/featured-alt-js.php';

// Préparation des hooks pour actions admin uniquement
if (is_admin()) {
    require_once AAM_PLUGIN_DIR . 'admin/batch.php';
    require_once AAM_PLUGIN_DIR . 'admin/metabox.php';
    require_once AAM_PLUGIN_DIR . 'admin/dashboard.php';
    require_once AAM_PLUGIN_DIR . 'includes/update-checker.php';
}

// Gestion des plans (gratuit/Pro)
// (voir includes/plans.php pour la logique)
