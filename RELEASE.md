# Procédure de release et mise à jour plugin Auto ALT Magic

## 1. Développement
- Développer sur une branche dédiée.
- Commits fréquents, messages explicites.
- Toujours versionner tous les fichiers utiles (code, assets, doc, scripts, update.json, etc.).

## 2. Préparation de la release
- Mettre à jour le numéro de version dans le header du fichier `auto-alt-magic.php` (ex : `Version: 1.0.1`).
- Mettre à jour le fichier `CHANGELOG.md`.
- Mettre à jour le fichier `update.json` :
  - `version` : nouvelle version
  - `download_url` : url du zip à publier (release GitHub ou serveur)
  - `changelog` : résumé des nouveautés/correctifs

## 3. Build du plugin
- Lancer le script : `./tools/build-plugin-zip.sh`
- Le zip généré s’appelle `auto-alt-magic-x.y.z.zip` (x.y.z = version courante).

## 4. Publication GitHub
- Taguer la version : `git tag v1.0.1 && git push --tags`
- Le workflow GitHub Actions `.github/workflows/release.yml` :
  - Build le zip
  - Met à jour `update.json` (version + url)
  - Publie le zip en release GitHub
  - Publie `update.json` sur la branche `gh-pages` (endpoint public)

## 5. Installation initiale sur WordPress
- Extensions > Ajouter > Téléverser une extension > Sélectionner le zip
- Installer et activer

## 6. Mise à jour automatique
- À chaque nouvelle release, le plugin détecte la nouvelle version via l’update checker natif.
- L’admin WP affiche “Mise à jour disponible” : cliquer “Mettre à jour”
- Les réglages et données sont conservés.

## 7. Bonnes pratiques
- Toujours tester la release sur un WP de staging avant diffusion large.
- Ne jamais exposer d’info sensible dans le zip ou l’update.json.
- Documenter chaque évolution dans le changelog.

## 8. Configuration GitHub Pages (endpoint update.json)
- La branche `gh-pages` est gérée automatiquement par le workflow.
- L’URL publique de `update.json` sera :
  `https://<user>.github.io/auto-alt-magic/update.json`
- Vérifier que cette URL est bien renseignée dans `includes/update-checker.php`.

---

**Cycle Dev → Release → Update WordPress = 100% automatisé, traçable, UX pro.**
