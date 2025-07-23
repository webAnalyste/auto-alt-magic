#!/bin/bash
# Script de build ZIP pour Auto ALT Magic
# Usage : ./tools/build-plugin-zip.sh

PLUGIN_SLUG="auto-alt-magic"
cd "$(dirname "$0")/.."
VERSION=$(grep "^Version:" "$PLUGIN_SLUG.php" | awk '{print $2}')
ZIP_NAME="$PLUGIN_SLUG-$VERSION.zip"

cd "$(dirname "$0")/.."

# Nettoyage
rm -f "$ZIP_NAME"

# Exclusion : .git, tools, .DS_Store, fichiers temporaires
zip -r "$ZIP_NAME" . \
    -x '*.git*' \
    -x 'tools/*' \
    -x '*.DS_Store' \
    -x '*.zip' \
    -x '*.md' \
    -x 'tests/*' \
    -x '*.log'

echo "ZIP généré : $ZIP_NAME"
