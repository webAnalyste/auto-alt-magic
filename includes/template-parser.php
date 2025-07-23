<?php
/**
 * Remplace les balises dynamiques dans un texte ALT personnalisé
 * Balises supportées :
 *   {{mot_cle}}, {{titre}}, {{nom_image}}, {{lang}}, {{type_post}}
 *
 * @param string $template Texte avec balises dynamiques
 * @param array $vars Tableau associatif : 'mot_cle', 'titre', 'nom_image', 'lang', 'type_post'
 * @return string Texte avec balises remplacées
 */
function aam_parse_template_tags($template, $vars = array()) {
    $default_vars = array(
        'mot_cle'   => '',
        'titre'     => '',
        'nom_image' => '',
        'lang'      => '',
        'type_post' => '',
    );
    $vars = array_merge($default_vars, $vars);
    $search = array();
    $replace = array();
    foreach ($vars as $key => $value) {
        $search[] = '{{' . $key . '}}';
        $replace[] = esc_attr($value); // Sécurité : échappement
    }
    return str_replace($search, $replace, $template);
}

// Exemple de test manuel (à retirer en prod)
// echo aam_parse_template_tags('Image de {{type_post}} : {{titre}} (mot-clé : {{mot_cle}})', [
//     'mot_cle' => 'randonnée',
//     'titre' => 'Top 10 équipements',
//     'type_post' => 'article',
// ]);
