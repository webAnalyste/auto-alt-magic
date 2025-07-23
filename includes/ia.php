<?php
/**
 * Génération ALT par IA (Pro)
 * @param array $args (image_name, post_title, mot_cle, mots_cles_secondaires, lang)
 * @return string|false
 */
/**
 * Génération ALT par IA (OpenAI, Pro)
 * @param array $args (image_name, post_title, mot_cle, mots_cles_secondaires, lang)
 * @return string|false
 */
function aam_generate_alt_ia($args) {
    // Récupérer clé OpenAI stockée en option (jamais loggée ni affichée)
    $api_key = get_option('aam_openai_api_key');
    if (empty($api_key) || strpos($api_key, 'sk-') !== 0) {
        return false;
    }
    // Préparer prompt contextuel
    $prompt = 'Génère un texte ALT SEO pertinent et naturel pour une image.';
    if (!empty($args['mot_cle'])) {
        $prompt .= ' Mot-clé principal : ' . $args['mot_cle'] . '.';
    }
    if (!empty($args['post_title'])) {
        $prompt .= ' Contexte : ' . $args['post_title'] . '.';
    }
    if (!empty($args['image_name'])) {
        $prompt .= ' Nom image : ' . $args['image_name'] . '.';
    }
    // Appel API OpenAI (gpt-3.5-turbo)
    $response = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body' => json_encode([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'Tu es un assistant expert en SEO, tu génères des textes ALT courts, naturels, pertinents, sans guillemets.'],
                ['role' => 'user', 'content' => $prompt],
            ],
            'max_tokens' => 60,
            'temperature' => 0.7,
        ]),
        'timeout' => 15,
    ]);
    if (is_wp_error($response)) {
        error_log('[AAM] Erreur OpenAI : ' . $response->get_error_message());
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (!empty($body['choices'][0]['message']['content'])) {
        return trim($body['choices'][0]['message']['content']);
    }
    error_log('[AAM] Erreur OpenAI : réponse inattendue');
    return false;
}
