SHELL := /bin/sh
COMPOSE := docker compose --env-file=src/.env

.PHONY: up down restart ps logs migrate test lint shell key

## First-time setup and start

build:
	@if [ ! -f src/.env ]; then \
		cp src/.env.example src/.env; \
		echo "Created src/.env"; \
	fi
	$(COMPOSE) build
	$(COMPOSE) up -d
	@echo "Installing Composer dependencies..."
	$(COMPOSE) run --rm app composer install --no-interaction
	@echo "Waiting for MySQL and Redis..."
	@i=0; until $(COMPOSE) exec -T mysql mysqladmin ping -h 127.0.0.1 -uroot -p"$$(grep '^DB_ROOT_PASSWORD=' src/.env | cut -d= -f2- | tr -d '"' )" --silent >/dev/null 2>&1; do \
		i=$$((i + 1)); \
		if [ $$i -ge 30 ]; then echo "MySQL is not ready"; exit 1; fi; \
		sleep 2; \
	done
	@if ! grep -q '^APP_KEY=.' src/.env; then \
		$(COMPOSE) exec -T app php artisan key:generate; \
	fi
	$(COMPOSE) exec -T app php artisan config:clear
	$(COMPOSE) exec -T app php artisan migrate --force

up:
	$(COMPOSE) up -d

stop:
	$(COMPOSE) stop

down:
	$(COMPOSE) down

restart:
	$(COMPOSE) restart

ps:
	$(COMPOSE) ps

logs:
	$(COMPOSE) logs -f app queue

seed:
	$(COMPOSE) exec -T app php artisan db:seed --force

migrate:
	$(COMPOSE) exec -T app php artisan migrate --force

test:
	$(COMPOSE) exec -T app php artisan test

lint:
	$(COMPOSE) exec -T app ./vendor/bin/pint --test

correct-lint:
	$(COMPOSE) exec -T app ./vendor/bin/pint

shell:
	$(COMPOSE) exec app sh

key:
	$(COMPOSE) exec app php artisan key:generate
