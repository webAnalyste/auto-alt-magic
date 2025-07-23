<?php
/*
Plugin Name: Auto ALT Magic
Description: Génération et injection automatique des attributs ALT et TITLE dans les balises <img> des contenus WordPress (posts, pages, produits, etc.).
Version: 1.0.0
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

// Chargement du cœur du plugin (structure à venir)
// require_once AAM_PLUGIN_DIR . 'includes/core.php';

// Hooks d'activation/désactivation (structure à venir)
// register_activation_hook(__FILE__, 'aam_activate');
// register_deactivation_hook(__FILE__, 'aam_deactivate');
