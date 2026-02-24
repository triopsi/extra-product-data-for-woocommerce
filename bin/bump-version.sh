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

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PACKAGE_JSON="$ROOT_DIR/package.json"
PLUGIN_PHP="$ROOT_DIR/extra-product-data-for-woocommerce.php"
COMPOSER_JSON="$ROOT_DIR/composer.json"
CONSTANTS_PHP="$ROOT_DIR/src/constants.php"

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

# update src/constants.php $version variable if present
if [ -f "$CONSTANTS_PHP" ]; then
  sed -E -i.bak "s/(\\\$version = ')[0-9]+\.[0-9]+\.[0-9]+'/\1$new_version'/" "$CONSTANTS_PHP"
fi

# update readme.txt stable tag if present
README="$ROOT_DIR/readme.txt"
if [ -f "$README" ]; then
  # Replace the Stable tag line with the new version
  sed -E -i.bak "s/^(Stable tag:[[:space:]]*).*/\1$new_version/" "$README"
  
  # Add new changelog entry with current date (DD.MM.YYYY format)
  current_date=$(date '+%d.%m.%Y')
  changelog_entry="= $new_version ($current_date) ="
  
  # Check if changelog entry already exists
  if ! grep -q "^= $new_version" "$README"; then
    # Find the line number after "== Changelog ==" and insert new entry
    # Insert after the first existing version entry (after the Changelog header)
    awk -v entry="$changelog_entry" '
      /^== Changelog ==/ { 
        print $0
        # Print empty line
        print ""
        # Print new version entry
        print entry
        # Add placeholder for changes
        print "* Changes will be documented here"
        in_changelog=1
        next
      }
      { print }
    ' "$README" > "$README.tmp" && mv "$README.tmp" "$README"
  fi
fi

# update README.md stable tag if present
README_MD="$ROOT_DIR/README.md"
if [ -f "$README_MD" ]; then
  # Replace the Stable tag line in the markdown table with the new version
  sed -E -i.bak "s/^(\| Stable tag \|[[:space:]]*).*/\1$new_version |/" "$README_MD"
fi

echo "Bumped version: $current_version -> $new_version"
exit 0
