#!/bin/bash
# Script test upload FTP unique (put)
FTP_HOST="147.79.103.225"
FTP_USER="u781218936"
FTP_PASS="@1512_Jdepp1969#1808#1"
REMOTE_DIR="/domains/training.webanalyste.com/public_html/wp-content/plugins/auto-alt-magic/"
LOCAL_FILE="$(dirname "$0")/../test-cascade.txt"

if ! command -v lftp >/dev/null 2>&1; then
  echo "[ERREUR] lftp n'est pas installé."
  exit 1
fi

set -x
lftp -u "$FTP_USER","$FTP_PASS" $FTP_HOST <<EOF
set ssl:verify-certificate no
cd "$REMOTE_DIR"
put "$LOCAL_FILE" -o test-cascade.txt
quit
EOF
set +x

echo "[OK] Fichier test-cascade.txt envoyé sur $FTP_HOST:$REMOTE_DIR"
