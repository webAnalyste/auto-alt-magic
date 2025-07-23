#!/bin/bash
# Script de déploiement FTP pour synchroniser le plugin Auto ALT Magic sur une instance WordPress de dev
# Usage : ./tools/deploy-dev.sh
# Paramètres à personnaliser ci-dessous

# === CONFIGURATION ===
FTP_HOST="147.79.103.225"
FTP_USER="u781218936"
FTP_PASS="@1512_Jdepp1969#1808#1"
REMOTE_DIR="/domains/training.webanalyste.com/public_html/wp-content/plugins/auto-alt-magic/"
LOCAL_DIR="/Users/fscan/Library/CloudStorage/GoogleDrive-franck@webanalyste.com/Mon Drive/webAnalyste/scripts/WindSurf/CascadeProjects/extensions WP/auto-alt-magic/"

# === DÉPLOIEMENT ===
if ! command -v lftp >/dev/null 2>&1; then
  echo "[ERREUR] lftp n'est pas installé. Installez-le avec : brew install lftp (macOS) ou apt install lftp (Linux)"
  exit 1
fi

lftp -u "$FTP_USER","$FTP_PASS" $FTP_HOST <<EOF
mirror -R --delete --verbose "$LOCAL_DIR" "$REMOTE_DIR"
quit
EOF

echo "[OK] Plugin synchronisé sur $FTP_HOST:$REMOTE_DIR depuis $LOCAL_DIR"
