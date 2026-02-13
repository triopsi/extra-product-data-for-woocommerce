#!/bin/bash
set -e
# Script assumes release tags are formatted as MAJOR.MINOR.PATCH
# For example: 1.2.3

# https://docs.github.com/en/actions/reference/workflows-and-actions/variables
# For workflows triggered by release, this is the release tag created.
TAG_VERSION="${1:-${GITHUB_REF#refs/tags/}}"

# Read the stable tag from readme.txt and compare it to the tag version. If they don't match, exit with an error.
README_VERSION=$(grep -m 1 "^Stable tag:" readme.txt | awk '{print $3}')

if [ "$TAG_VERSION" != "$README_VERSION" ]; then
    echo "Version mismatch: Tag version ($TAG_VERSION) does not match readme.txt stable tag ($README_VERSION)"
    exit 1
fi

echo "Version check passed: $TAG_VERSION matches readme.txt stable tag."
