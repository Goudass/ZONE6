.PHONY: up down setup restart logs open

up:
	@bash scripts/start-local.sh

down:
	docker compose down

setup:
	@bash scripts/setup-wordpress.sh

restart:
	docker compose restart

logs:
	docker compose logs -f wordpress

open:
	@echo "Front:  http://localhost:8080"
	@echo "Admin:  http://localhost:8080/wp-admin"
