<?php
/**
 * Récupère le mot-clé principal (focus keyword) depuis les plugins SEO majeurs.
 * Prend en charge Yoast, Rank Math, SEOPress, AIOSEO.
 * Retourne false si aucun mot-clé trouvé (fallback manuel à prévoir dans la logique appelante).
 *
 * @param int $post_id
 * @return string|false
 */
function aam_get_focus_keyword($post_id) {
    // Yoast SEO
    $yoast_kw = get_post_meta($post_id, '_yoast_wpseo_focuskw', true);
    if (!empty($yoast_kw)) return sanitize_text_field($yoast_kw);

    // Rank Math
    $rankmath_kw = get_post_meta($post_id, 'rank_math_focus_keyword', true);
    if (!empty($rankmath_kw)) return sanitize_text_field($rankmath_kw);

    // SEOPress
    $seopress_kw = get_post_meta($post_id, '_seopress_analysis_target_kw', true);
    if (!empty($seopress_kw)) return sanitize_text_field($seopress_kw);

    // AIOSEO
    $aioseo_kw = get_post_meta($post_id, '_aioseo_focus_keyphrase', true);
    if (!empty($aioseo_kw)) return sanitize_text_field($aioseo_kw);

    // Aucun mot-clé trouvé
    return false;
}
