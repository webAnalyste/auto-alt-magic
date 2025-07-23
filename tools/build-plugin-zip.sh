#!/bin/bash
# Script de build ZIP pour Auto ALT Magic
# Usage : ./tools/build-plugin-zip.sh

PLUGIN_SLUG="auto-alt-magic"
cd "$(dirname \"$0\")/.."
VERSION=$(grep "^Version:" "$PLUGIN_SLUG.php" | awk '{print $2}')
ZIP_NAME="$PLUGIN_SLUG-$VERSION.zip"

cd "$(dirname \"$0\")/.."

# Nettoyage
rm -f "$ZIP_NAME"

# Créer un dossier temporaire pour le packaging
TMP_DIR="/tmp/$PLUGIN_SLUG-build-$$"
mkdir -p "$TMP_DIR/$PLUGIN_SLUG"

# Copier tous les fichiers/dossiers du plugin dans le dossier racine
shopt -s extglob
cp -R !(tools|.git|*.zip|*.md|tests|*.log|.DS_Store) "$TMP_DIR/$PLUGIN_SLUG/"

# Créer le zip avec le dossier racine
cd "$TMP_DIR"
zip -r "$ZIP_NAME" "$PLUGIN_SLUG"

# Déplacer le zip généré dans le dossier du plugin
mv "$ZIP_NAME" "$OLDPWD/$ZIP_NAME"

# Nettoyage
cd /
rm -rf "$TMP_DIR"

echo "ZIP généré : $ZIP_NAME (avec dossier racine $PLUGIN_SLUG/)"
