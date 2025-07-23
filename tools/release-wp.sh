#!/bin/bash
# Script unique pour automatiser la release du plugin gratuit Auto ALT Magic (cycle Dev → Release → Update WordPress)
# Usage : ./tools/release-wp.sh <version>
# Exemple : ./tools/release-wp.sh 1.0.3

set -e

if [ -z "$1" ]; then
  echo "Usage: $0 <version> (ex: 1.0.3)"
  exit 1
fi
VERSION="$1"
ZIP="auto-alt-magic-$VERSION.zip"
REPO="https://github.com/webAnalyste/auto-alt-magic.git"
BRANCH="main"

# 1. Bump version plugin principal
sed -i '' "s/^Version: .*/Version: $VERSION/" auto-alt-magic/auto-alt-magic.php

# 2. Bump update.json
cat > auto-alt-magic/update.json <<EOL
{
  "version": "$VERSION",
  "download_url": "https://github.com/webAnalyste/auto-alt-magic/releases/download/v$VERSION/$ZIP",
  "changelog": "Release $VERSION : voir changelog GitHub."
}
EOL

# 3. Commit, tag, push
cd "$(dirname "$0")/.."
git add .
git commit -am "release: v$VERSION"
git tag v$VERSION
git push
git push --tags

# 4. Générer le ZIP
bash tools/build-plugin-zip.sh
mv auto-alt-magic-*.zip $ZIP

# 5. Publier la release GitHub (si gh CLI dispo)
if command -v gh >/dev/null 2>&1; then
  gh release create v$VERSION $ZIP --title "v$VERSION" --notes "Release $VERSION : voir changelog GitHub."
else
  echo "[INFO] Release GitHub non publiée automatiquement (gh CLI non trouvée). Faire la release manuellement si besoin."
fi

# 6. Export ZIP free pour archivage/distribution
EXPORT_DIR="../exports"
mkdir -p "$EXPORT_DIR"
cp -f "$ZIP" "$EXPORT_DIR/"
echo "ZIP exporté dans $EXPORT_DIR/$ZIP"

echo "Release $VERSION terminée. Le plugin sera proposé en mise à jour sur tous les WordPress connectés à update.json."
