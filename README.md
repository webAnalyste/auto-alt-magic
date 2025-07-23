# Auto ALT Magic

Génération automatique des attributs ALT et TITLE pour les images dans WordPress, compatible WooCommerce, SEO, IA.

---

## Fonctionnalités principales
- Génération contextuelle des ALT/TITLE dans le contenu (jamais dans la médiathèque)
- Méthodes : titre du post, nom du fichier image, texte libre avec balises dynamiques
- UI admin moderne : réglages globaux par type de contenu (onglets), metabox contextuelle
- Compatible SEO (Yoast, Rank Math, SEOPress, AIOSEO)
- Compatible WooCommerce, CPT, multisite
- Sécurité et versioning stricts, conformité SaaS
- Mise à jour automatique via endpoint public (update.json)

## Installation
1. Télécharger le ZIP généré (`auto-alt-magic-x.y.z.zip`)
2. Extensions > Ajouter > Téléverser une extension > Sélectionner le ZIP
3. Installer et activer le plugin

## Mise à jour automatique
- À chaque nouvelle version, le plugin détecte la mise à jour via l’admin WP
- Un clic sur “Mettre à jour” suffit (aucune perte de réglages)

## Procédure de release (dev)
- Mettre à jour le numéro de version dans `auto-alt-magic.php`
- Mettre à jour `CHANGELOG.md` et `update.json`
- Générer le ZIP : `./tools/build-plugin-zip.sh`
- Taguer la version : `git tag v1.0.1 && git push --tags`
- Le workflow CI build, release et publie tout automatiquement (ZIP + update.json)

## Documentation
- Voir `RELEASE.md` pour la procédure détaillée Dev → Release → Update
- Voir `CHANGELOG.md` pour l’historique des versions

## Support & Contributions
- Toute évolution doit suivre les règles de sécurité, versioning et tests systématiques (voir SECURITE-REGLES.md)
- Problème ou suggestion ? Ouvrir une issue ou une PR

---

© webAnalyste – Tous droits réservés
