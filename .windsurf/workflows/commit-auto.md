---
description: Commit GIT automatique AVANT chaque modification
---

# Workflow commit auto avant modification (RÈGLE D'OR)

1. **Avant toute modification du code, exécutez :**
   ```bash
   git add .
   git commit -m "backup auto avant modification [date/heure/raison explicite]"
   ```
2. **Vérifiez que le commit a bien été créé** :
   ```bash
   git log -1
   ```
   Le message doit contenir "backup auto avant modification" et la date/heure.
3. **Si le commit échoue (erreur, conflit, etc.), NE PAS MODIFIER le code tant que le problème n’est pas résolu.**
4. **Après chaque modification majeure, refaire un commit explicite.**
5. **Vérifiez régulièrement l’historique pour garantir la traçabilité :**
   ```bash
   git log --oneline --grep="backup auto"
   ```
6. **Documentez tout écart ou bug dans un fichier README ou CHANGELOG.**

> Ce workflow doit être appliqué strictement AVANT chaque modification, sans exception, pour respecter les règles d'or de sécurité et de traçabilité.
