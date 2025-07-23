<?php
/**
 * Update checker pour Auto ALT Magic (auto-update via update.json)
 * Basé sur le schéma WordPress natif
 */
add_filter('site_transient_update_plugins', function($transient) {
    if (empty($transient->checked)) return $transient;
    $plugin_slug = 'auto-alt-magic/auto-alt-magic.php';
    if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $current_version = get_plugin_data(WP_PLUGIN_DIR . '/auto-alt-magic/auto-alt-magic.php')['Version'];
    $update_url = 'https://github.com/webAnalyste/auto-alt-magic/raw/main/update.json'; // À adapter selon endpoint public
    $response = wp_remote_get($update_url, ['timeout' => 10]);
    if (is_wp_error($response)) return $transient;
    $data = json_decode(wp_remote_retrieve_body($response));
    if (!isset($data->version) || version_compare($current_version, $data->version, '>=')) return $transient;
    $obj = new stdClass();
    $obj->slug = 'auto-alt-magic';
    $obj->plugin = $plugin_slug;
    $obj->new_version = $data->version;
    $obj->url = 'https://github.com/webAnalyste/auto-alt-magic';
    $obj->package = $data->download_url;
    $transient->response[$plugin_slug] = $obj;
    return $transient;
});

add_filter('plugins_api', function($res, $action, $args) {
    if ($action !== 'plugin_information' || $args->slug !== 'auto-alt-magic') return $res;
    $update_url = 'https://github.com/webAnalyste/auto-alt-magic/raw/main/update.json';
    $response = wp_remote_get($update_url, ['timeout' => 10]);
    if (is_wp_error($response)) return $res;
    $data = json_decode(wp_remote_retrieve_body($response));
    if (!$data) return $res;
    $obj = new stdClass();
    $obj->name = 'Auto ALT Magic';
    $obj->slug = 'auto-alt-magic';
    $obj->version = $data->version;
    $obj->author = '<a href="https://webanalyste.com">webAnalyste</a>';
    $obj->homepage = 'https://github.com/webAnalyste/auto-alt-magic';
    $obj->download_link = $data->download_url;
    $obj->sections = [
        'description' => 'Génération automatique ALT/TITLE images, compatible WooCommerce, SEO, IA.',
        'changelog' => $data->changelog
    ];
    return $obj;
}, 10, 3);
