# 📋 Référence des paramètres Auto ALT Magic

Ce document liste tous les paramètres (Free + Pro) à intégrer, tester et documenter dans le plugin. Il sert de checklist de développement et de QA.

| Catégorie | Paramètre | Type | Description | Plan |
|-----------|-----------|------|-------------|------|
| 🔎 Ciblage des images | Traiter uniquement les images sans alt | bool (ON/OFF) | Ne cible que les images sans attribut alt | Free + Pro |
|  | Traiter toutes les images | bool | Remplace ou ajoute un alt même s’il existe déjà | Free + Pro |
|  | Cibler les alt correspondant à une regexp | input texte | Exemple : `^(image|photo)` | Free + Pro |
|  | Longueur mini du alt pour être considéré comme “vide” | number | Ex : 5 → un alt de moins de 5 caractères est régénéré | Pro |
| 🧠 Méthode de génération | Source du texte alt | select | titre, nom_image, texte_libre, IA | Free + Pro |
|  | Texte libre personnalisé | textarea | Utilise balises dynamiques ({{titre}}, {{mot_cle}}, etc.) | Free + Pro |
|  | Balises dynamiques activées | info | {{mot_cle}}, {{titre}}, {{nom_image}}, {{lang}}, {{type_post}} | Free |
|  | Préfixe | input texte | Ajouté avant le texte généré | Free + Pro |
|  | Suffixe | input texte | Ajouté après le texte généré | Free + Pro |
| 🌍 Langue | Langue du texte généré | select | Ex : fr, en, es… | Free |
|  | Détection automatique de langue | bool | Utilise la langue du post (ou Polylang, WPML, etc.) | Pro |
| 🏷 Mot-clé | Mot-clé principal (manuel) | input texte | Utilisé dans {{mot_cle}} | Free |
|  | Extraction auto depuis plugin SEO | bool (auto) | Support de Yoast, Rank Math, SEOPress, AIOSEO | Free |
| 🧾 Attribut title | Copier automatiquement le alt dans title | bool | Si title absent, généré à partir de alt | Free |
|  | Forcer remplacement du title existant | bool | Écrase le title même s’il est présent | Pro |
| 🕹 Déclencheurs | À la sauvegarde du post (save_post) | bool | Traitement automatique création/modif post | Free + Pro |
|  | Traitement manuel dans l’éditeur | bouton | Bouton “Générer les ALT maintenant” | Free + Pro |
|  | Traitement par lot dans la liste des posts | action groupée | “Appliquer à la sélection” admin | Pro |
|  | Page d’outils pour traitement massif | interface dédiée | Lancer des traitements sur lots | Pro |
|  | Mode dry-run (prévisualisation sans enregistrer) | bool | Simule le traitement | Free + Pro |
|  | Rollback (restauration du contenu précédent) | bool | Sauvegarde ancienne version post_content | Free + Pro |
| 🗂 Types de contenu (CPT) | Activer pour ce post type | checkbox par type | post, page, product, portfolio, etc. | Free + Pro |
|  | Méthode spécifique par post type | select par type | Peut différer entre post, page, etc. | Free + Pro |
|  | Texte libre par type | textarea par type | Modèle dynamique personnalisé par type | Free + Pro |
|  | Préfixe/suffixe par type | input texte | Adapter structure alt selon type | Free + Pro |
| 🖼 Champs personnalisés | Activer le traitement des champs image personnalisés | bool | Inclut images ACF, MetaBox, Pods, JetEngine | Pro |
|  | Sélectionner les champs image à traiter | multi-select | _thumbnail_id, galerie_images, etc. | Pro |
|  | Détection automatique des champs image ACF | oui (auto) | get_field_objects() | Pro |
| 🤖 IA | Utiliser la génération IA | bool | Active l’appel à l’API IA | Pro |
|  | Prompt IA personnalisé | template (caché) | Composé avec titre, mot_cle, nom_image, lang, etc. | Pro |
|  | Choix de l’API IA | select | OpenAI, Mistral, Cloud Run perso | Pro |
| 🪙 Crédits IA | Quota mensuel | compteur interne | 500, 2000 ou 10 000 requêtes / mois | Pro |
|  | Achat de packs IA sans expiration | bouton + compteur | Packs 1k / 5k / 10k crédits | Pro |
|  | Affichage des crédits restants | admin notice / widget | Visibles dans l’admin | Pro |

## ✅ Notes complémentaires
- Les options par post type sont imbriquées dynamiquement dans l’interface (ex : onglets post, page, product, etc.).
- Les champs personnalisés sont détectés automatiquement pour ACF / Pods / Meta Box / JetEngine en plan Pro.
- L’interface admin doit proposer un mode simple (rapide à configurer) + un mode avancé pour granularité.

---

**Ce document doit être mis à jour à chaque ajout/correction de paramètre ou d’option dans le plugin.**
