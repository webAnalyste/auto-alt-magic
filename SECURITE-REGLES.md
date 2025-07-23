# RÈGLES D'OR SÉCURITÉ & VERSIONING

## 1. Sécurité du site WordPress
- Ne jamais désactiver ni contourner les protections natives WordPress (nonces, capabilities, user_can, etc.).
- Toujours valider et échapper toutes les entrées/sorties utilisateur (sanitize_*, esc_*).
- Utiliser les fonctions natives WordPress pour toute manipulation BDD ou fichiers (wpdb, WP_Filesystem, etc.).
- Vérifier systématiquement les droits utilisateur avant toute action sensible (écriture, suppression, batch).
- Ne jamais exposer d’informations sensibles dans l’admin, les logs ou les réponses AJAX.
- Toujours charger les fichiers du plugin via ABSPATH (anti-inclusion directe).
- Respecter les standards WP_DEBUG, i18n, et la compatibilité multisite.

## 2. Sécurité du code plugin
- Aucun eval(), shell_exec(), exec(), ni fonction dangereuse non justifiée.
- Pas de dépendance externe non auditée ou non versionnée dans le dépôt.
- Séparer strictement le code admin et public (aucun traitement en frontend).
- Prévoir des hooks pour rollback ou désactivation sans perte de données critiques.
- Documenter chaque point d’extension, hook, ou filtre pour éviter les usages à risque.

## 3. Versioning GIT systématique
- Avant chaque modification (prompt/action), effectuer un commit GIT du code actuel ("backup auto avant modification").
- Après chaque modification, commit explicite et push immédiat sur le remote.
- Aucun fichier utile au plugin ne doit rester hors versioning (code, assets, doc, configs, scripts, données).
- Les branches de dev doivent être mergées uniquement après validation/test.
- Historique GIT = traçabilité complète, retour arrière possible à chaque étape.

## 4. Réversibilité
- Toujours prévoir la possibilité de rollback (restauration état précédent via GIT ou hook plugin).
- Documenter toute action impactant la structure ou les données (README, changelog).

## 5. Surveillance et alertes
- Activer WP_DEBUG_LOG en dev, mais ne jamais laisser d’erreur fatale ou warning en prod.
- Prévoir une page d’état/santé du plugin (logs, quotas, erreurs critiques).


## 6. Tests systématiques
- Toute action, fonctionnalité ou correctif doit être systématiquement testé (test manuel ou automatisé) avant et après application.
- Documenter le résultat du test (succès/échec, logs, captures si pertinent).
- En cas d’échec, rollback immédiat via GIT ou mécanisme plugin, et analyse de la cause.
- Les tests doivent couvrir la sécurité, la non-régression, la compatibilité et la performance.

---

**Toute action ou prompt doit respecter ces règles d’or.**

> Toute dérogation doit être explicitement justifiée, documentée et validée avant application.
