# Limitation WordPress : injection ALT sur la featured image

## Problème

La fonction `post_thumbnail_html` (et le filtre associé) ne permet pas toujours de surcharger l’attribut ALT/TITLE de la balise `<img>` générée pour la featured image sur tous les thèmes WordPress. Certains thèmes, constructeurs (Gutenberg, Elementor, Divi…) ou hooks personnalisés contournent ce filtre ou génèrent leur propre balisage, ce qui rend l’injection ALT impossible côté plugin, même avec un code propre.

## Diagnostic
- Testé : injection ALT/TITLE sur images dans le contenu (OK)
- Testé : injection ALT/TITLE sur featured image via filtre (KO sur certains thèmes/pages)
- Le HTML généré côté front ne passe pas toujours par `post_thumbnail_html`.

## Solutions possibles
- Utiliser un thème respectant les standards WP (the_post_thumbnail, get_the_post_thumbnail).
- Pour les thèmes/builders qui ne respectent pas ce flux, il n’existe pas de solution universelle plugin-side (hors patch ou override du thème).
- Pour WooCommerce : même limitation si le template produit surcharge la sortie image.

## Recommandation
- Documenter cette limitation dans le README et la FAQ.
- Conseiller l’usage de thèmes compatibles WP standards pour garantir l’injection ALT automatique sur la featured image.
- Ne jamais patcher le thème ni injecter de JS en front (mauvaise pratique SEO/UX).

## Pour aller plus loin
- Proposer une liste blanche de thèmes compatibles.
- Ajouter un check dans l’admin pour prévenir l’utilisateur si le ALT n’est pas injecté côté front (option Pro).
