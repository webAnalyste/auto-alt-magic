<?php
/**
 * Dashboard Pro : quotas, dry-run, logs, crédits IA
 * @return void
 */
function aam_dashboard_pro() {
    // TODO : dashboard avancé Pro
}

// Ajout bouton admin "Générer ALT maintenant" (manual trigger)
add_action('post_submitbox_misc_actions', function() {
    global $post;
    if (!in_array($post->post_type, ['post', 'page', 'product'])) return;
    if (!current_user_can('edit_post', $post->ID)) return;
    $url = add_query_arg([
        'aam_generate_alt' => 1,
        'post' => $post->ID,
        '_wpnonce' => wp_create_nonce('aam_generate_alt_' . $post->ID)
    ]);
    echo '<div class="misc-pub-section"><a href="' . esc_url($url) . '" class="button">Générer ALT maintenant</a></div>';
});

// Traitement du bouton manuel
add_action('admin_init', function() {
    if (isset($_GET['aam_generate_alt'], $_GET['post'], $_GET['_wpnonce'])) {
        $post_id = intval($_GET['post']);
        if (!wp_verify_nonce($_GET['_wpnonce'], 'aam_generate_alt_' . $post_id)) return;
        if (!current_user_can('edit_post', $post_id)) return;
        $post = get_post($post_id);
        if ($post) {
            // Dry-run (preview) si demandé
            if (isset($_GET['dry_run'])) {
                // TODO : afficher preview sans sauvegarder
            } else {
                // Génération effective
                require_once AAM_PLUGIN_DIR . 'includes/core.php';
                aam_core_process_post($post_id, $post);
                // Redirection avec notice succès
                wp_redirect(add_query_arg('aam_alt_done', 1, get_edit_post_link($post_id, '')));
                exit;
            }
        }
    }
});

// Affichage notice succès
add_action('admin_notices', function() {
    if (isset($_GET['aam_alt_done'])) {
        echo '<div class="notice notice-success is-dismissible"><p>ALT générés avec succès.</p></div>';
    }
});

// (Stub) Rollback : à compléter pour restaurer le contenu précédent
// TODO : sauvegarder l’ancien contenu avant modif et proposer restauration
