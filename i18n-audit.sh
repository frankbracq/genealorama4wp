#!/usr/bin/env bash
set -euo pipefail

ROOT="/Users/Frank/Documents/GitHub/genealorama4wp"
cd "$ROOT"

echo "==> Vérification en-tête du plugin (Text Domain / Domain Path)"
grep -RIn --include='*.php' -E '^\s*\*\s*Text Domain:|^\s*\*\s*Domain Path:' . || true

echo -e "\n==> Présence du chargement du textdomain"
grep -RIn --include='*.php' -E 'load_plugin_textdomain\s*\(' . || echo "Manque: load_plugin_textdomain()"

echo -e "\n==> Chaînes PHP potentiellement non traduites (echo/texte brut)"
grep -RIn --include='*.php' -E "echo\s+['\"][^'\"]+['\"]|>\s*[^<>\s][^<>]*\s*<" . \
  | grep -v -E "__\(|_e\(|esc_html__\(|esc_html_e\(|_x\(|_ex\(|_n\(" || true

echo -e "\n==> Appels i18n PHP repérés (statistiques rapides)"
grep -RIn --include='*.php' -E "__\(|_e\(|esc_html__\(|esc_html_e\(|_x\(|_ex\(|_n\(" . | wc -l

echo -e "\n==> JS i18n (import @wordpress/i18n + wp_set_script_translations)"
grep -RIn --include='*.js' -E "from '@wordpress/i18n'|wp_set_script_translations\s*\(" . || true

echo -e "\n==> Fichiers block.json avec textdomain"
grep -RIn --include='block.json' -E '"textdomain"\s*:\s*"' . || true

echo -e "\n==> Dossier languages et fichiers .pot/.po/.mo"
find . -type f \( -name "*.pot" -o -name "*.po" -o -name "*.mo" \) -print || true

echo -e "\n==> Conseillé: génération du .pot (WP-CLI requis)"
echo "wp i18n make-pot . languages/secure-iframe-embed-for-genealorama.pot --slug=secure-iframe-embed-for-genealorama"