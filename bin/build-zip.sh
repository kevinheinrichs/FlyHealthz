#!/usr/bin/env bash
# Builds dist/flywp-health.zip for wordpress.org: folder flywp-health/, runtime files only.
set -euo pipefail
cd "$(dirname "$0")/.."
tmp=$(mktemp -d)
mkdir "$tmp/flywp-health"
cp -r flywp-health.php uninstall.php readme.txt includes "$tmp/flywp-health/"
mkdir -p dist
rm -f dist/flywp-health.zip
(cd "$tmp" && find flywp-health -exec touch -d '2026-09-23 00:00:00' {} + && zip -qrX - flywp-health) > dist/flywp-health.zip
rm -rf "$tmp"
unzip -l dist/flywp-health.zip
sha256sum dist/flywp-health.zip
