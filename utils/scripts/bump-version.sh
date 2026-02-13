#!/usr/bin/env bash
set -euo pipefail

# bump-version.sh
# Usage:
#   bump-version.sh patch
#   bump-version.sh minor
#   bump-version.sh major
#   bump-version.sh 1.2.3
#
# Updates the `version` field in package.json and the plugin header Version in
# extra-product-data-for-woocommerce.php. Prints the new version.

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
PACKAGE_JSON="$ROOT_DIR/package.json"
PLUGIN_PHP="$ROOT_DIR/extra-product-data-for-woocommerce.php"
COMPOSER_JSON="$ROOT_DIR/composer.json"

if [ "$#" -ne 1 ]; then
  echo "Usage: $0 {patch|minor|major|<version>}"
  exit 2
fi

ARG="$1"

# read current version from package.json
current_version=$(grep -oP '"version"\s*:\s*"\K[0-9]+\.[0-9]+\.[0-9]+' "$PACKAGE_JSON")
if [ -z "$current_version" ]; then
  echo "Could not read current version from $PACKAGE_JSON"
  exit 1
fi

IFS='.' read -r major minor patch <<< "$current_version"

increment() {
  case "$1" in
    patch)
      patch=$((patch + 1))
      ;;
    minor)
      minor=$((minor + 1))
      patch=0
      ;;
    major)
      major=$((major + 1))
      minor=0
      patch=0
      ;;
    *)
      echo "Unknown increment: $1"
      exit 2
      ;;
  esac
}

validate_version() {
  # basic semver x.y.z check
  if [[ "$1" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    return 0
  fi
  return 1
}

if [[ "$ARG" =~ ^(patch|minor|major)$ ]]; then
  increment "$ARG"
  new_version="$major.$minor.$patch"
elif validate_version "$ARG"; then
  new_version="$ARG"
else
  echo "Invalid argument. Provide 'patch', 'minor', 'major', or a version like 1.2.3"
  exit 2
fi

# update package.json
# Use jq if available for safe edit, otherwise fallback to sed
if command -v jq >/dev/null 2>&1; then
  tmpfile=$(mktemp)
  jq --arg v "$new_version" '.version=$v' "$PACKAGE_JSON" > "$tmpfile"
  mv "$tmpfile" "$PACKAGE_JSON"
else
  # naive sed replace of the version string
  sed -E -i.bak "s/(\"version\"[[:space:]]*:[[:space:]]*)\"[0-9]+\.[0-9]+\.[0-9]+\"/\1\"$new_version\"/" "$PACKAGE_JSON"
fi

# update composer.json if present
if [ -f "$COMPOSER_JSON" ]; then
  if command -v jq >/dev/null 2>&1; then
    tmpfile_comp=$(mktemp)
    jq --arg v "$new_version" '.version=$v' "$COMPOSER_JSON" > "$tmpfile_comp"
    mv "$tmpfile_comp" "$COMPOSER_JSON"
  else
    sed -E -i.bak "s/(\"version\"[[:space:]]*:[[:space:]]*)\"[0-9]+\.[0-9]+\.[0-9]+\"/\1\"$new_version\"/" "$COMPOSER_JSON"
  fi
fi

# update extra-product-data-for-woocommerce.php plugin header 'Version:' line
if [ -f "$PLUGIN_PHP" ]; then
  # Replace the Version: line within the plugin header block. Handles lines like
  # " * Version:    1.0.4" by allowing an optional leading '*' and whitespace.
  # Use awk to reliably replace the Version line within the opening PHPDoc block
  tmpfile="$(mktemp)"
  awk -v ver="$new_version" '
    BEGIN { in_header=0 }
    {
      if ( $0 ~ /^\s*\/\*\*/ ) { in_header=1 }
      if ( in_header && $0 ~ /Version:/ ) {
        print " * Version: " ver
        next
      }
      if ( in_header && $0 ~ /\*\// ) { in_header=0 }
      print $0
    }
  ' "$PLUGIN_PHP" > "$tmpfile"
  mv "$tmpfile" "$PLUGIN_PHP"
fi

# update readme.txt stable tag if present
README="$ROOT_DIR/readme.txt"
if [ -f "$README" ]; then
  # Replace the Stable tag line with the new version
  sed -E -i.bak "s/^(Stable tag:[[:space:]]*).*/\1$new_version/" "$README"
fi

echo "Bumped version: $current_version -> $new_version"
exit 0
