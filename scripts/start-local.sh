#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

if ! command -v docker >/dev/null 2>&1; then
  echo "❌ Docker nie jest zainstalowany."
  echo "   Zainstaluj Docker Desktop: https://www.docker.com/products/docker-desktop/"
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  echo "✅ Utworzono plik .env z domyślnymi ustawieniami"
fi

# shellcheck disable=SC1091
source .env
WP_PORT="${WP_PORT:-8080}"

echo "🚀 Uruchamiam WordPress lokalnie..."
docker compose up -d

echo "⏳ Czekam na gotowość WordPress..."
for i in $(seq 1 60); do
  if curl -sf "http://localhost:${WP_PORT}" >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

echo "⚙️  Konfiguruję WordPress i motyw..."
bash "$ROOT_DIR/scripts/setup-wordpress.sh"

echo ""
echo "✅ Blog działa lokalnie!"
echo "   Front:  http://localhost:${WP_PORT}"
echo "   Admin:  http://localhost:${WP_PORT}/wp-admin"
echo "   Login:  admin / admin123  (zmień w pliku .env)"
echo ""
echo "Zatrzymanie: docker compose down"
