#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if [ -f .env ]; then
  # shellcheck disable=SC1091
  source .env
fi

WP_URL="${WP_URL:-http://localhost:8080}"
WP_TITLE="${WP_TITLE:-ZONE6.PL}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin123}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@example.com}"

wp() {
  docker compose run --rm wpcli "$@"
}

echo "→ Sprawdzam instalację WordPress..."

if ! wp core is-installed >/dev/null 2>&1; then
  wp core install \
    --url="$WP_URL" \
    --title="$WP_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
  echo "✅ WordPress zainstalowany"
else
  echo "✅ WordPress już zainstalowany"
fi

wp option update blogdescription "Outdoor, trasy i przygody — 6zone.pl"
wp rewrite structure '/%postname%/' --hard
wp rewrite flush --hard

echo "→ Aktywuję motyw..."
wp theme activate adventure-blog

echo "→ Tworzę strony..."
create_page() {
  local slug="$1"
  local title="$2"
  local content="${3:-}"

  local id
  id="$(wp post list --post_type=page --name="$slug" --field=ID --format=csv 2>/dev/null || true)"
  if [ -z "$id" ]; then
    id="$(wp post create --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --post_content="$content" --porcelain)"
    echo "  + $title (ID $id)"
  else
    echo "  = $title już istnieje (ID $id)"
  fi
  echo "$id"
}

HOME_ID="$(create_page "start" "Start" "")"
NEWS_ID="$(create_page "aktualnosci" "Aktualności" "Najnowsze wpisy i newsy ze szlaków.")"
ABOUT_ID="$(create_page "o-mnie" "O mnie" "")"
CONTACT_ID="$(create_page "kontakt" "Kontakt" "Masz pytanie? Napisz przez formularz obok.")"

wp option update show_on_front page
wp option update page_on_front "$HOME_ID"
wp option update page_for_posts "$NEWS_ID"

echo "→ Tworzę menu..."
MENU_ID="$(wp menu list --fields=term_id,name --format=csv | grep ',Menu główne$' | cut -d, -f1 || true)"
if [ -z "$MENU_ID" ]; then
  wp menu create "Menu główne"
  MENU_ID="$(wp menu list --fields=term_id,name --format=csv | grep ',Menu główne$' | cut -d, -f1)"
fi

wp menu item add-post "$MENU_ID" "$ABOUT_ID" >/dev/null 2>&1 || true
wp menu item add-post "$MENU_ID" "$NEWS_ID" >/dev/null 2>&1 || true
wp menu item add-custom "$MENU_ID" "Trasy rowerowe" "${WP_URL}/typ-trasy/trasy-rowerowe/" >/dev/null 2>&1 || true
wp menu item add-custom "$MENU_ID" "Tatry" "${WP_URL}/typ-trasy/tatry/" >/dev/null 2>&1 || true
wp menu item add-custom "$MENU_ID" "Projekty" "${WP_URL}/typ-trasy/projekty/" >/dev/null 2>&1 || true
wp menu item add-post "$MENU_ID" "$CONTACT_ID" >/dev/null 2>&1 || true
wp menu location assign "$MENU_ID" primary >/dev/null 2>&1 || true
wp menu location assign "$MENU_ID" footer >/dev/null 2>&1 || true

echo "→ Importuję przykładowy GPX..."
GPX_ID="$(wp media import "$ROOT_DIR/wp-content/themes/adventure-blog/sample-data/sample-route.gpx" --porcelain 2>/dev/null || wp post list --post_type=attachment --name=sample-route --field=ID --format=csv | head -1)"

echo "→ Dodaję przykładową trasę..."
ROUTE_ID="$(wp post list --post_type=trasa --name=przykladowa-trasa-tatrzanska --field=ID --format=csv 2>/dev/null || true)"
if [ -z "$ROUTE_ID" ]; then
  ROUTE_ID="$(wp post create \
    --post_type=trasa \
    --post_status=publish \
    --post_title='Przykładowa trasa tatrzańska' \
    --post_name='przykladowa-trasa-tatrzanska' \
    --post_content='To przykładowa trasa demo. Podmień opis, zdjęcia i plik GPX na własne dane ze Stravy lub Komoot.' \
    --porcelain)"

  wp post meta update "$ROUTE_ID" _adventure_featured 1
  wp post meta update "$ROUTE_ID" _adventure_trudnosc srednia
  wp post meta update "$ROUTE_ID" _adventure_czas "3h 15min"
  wp post meta update "$ROUTE_ID" _adventure_dystans "18 km"
  wp post meta update "$ROUTE_ID" _adventure_przewyzszenie "650 m"
  if [ -n "$GPX_ID" ]; then
    wp post meta update "$ROUTE_ID" _adventure_gpx_id "$GPX_ID"
  fi
  wp term add "$ROUTE_ID" typ-trasy tatry >/dev/null 2>&1 || true
  echo "  + Trasa demo (ID $ROUTE_ID)"
else
  echo "  = Trasa demo już istnieje (ID $ROUTE_ID)"
fi

echo "→ Dodaję przykładową aktualność..."
POST_ID="$(wp post list --post_type=post --name=witaj-na-blogu --field=ID --format=csv 2>/dev/null || true)"
if [ -z "$POST_ID" ]; then
  wp post create \
    --post_type=post \
    --post_status=publish \
    --post_title='Witaj na blogu!' \
    --post_name='witaj-na-blogu' \
    --post_content='To przykładowy wpis w sekcji Aktualności. Możesz go edytować lub usunąć w panelu WordPress.' \
    >/dev/null
  echo "  + Wpis demo"
fi

echo "✅ Konfiguracja lokalna gotowa"
