DC = docker compose

.PHONY: build up down restart logs bash install db-create db-drop db-reset migrate migrate-test test

build:
	$(DC) build

up:
	$(DC) up -d

down:
	$(DC) down

restart:
	$(DC) down && $(DC) up -d

logs:
	$(DC) logs -f --tail=100

bash:
	$(DC) exec php bash

install:
	$(DC) exec php sh -lc "cd app && composer install"

db-create:
	$(DC) exec php sh -lc "cd app && php bin/console doctrine:database:create --if-not-exists"

db-drop:
	$(DC) exec php sh -lc "cd app && php bin/console doctrine:database:drop --if-exists --force"

db-reset: db-drop db-create migrate

migrate:
	$(DC) exec php sh -lc "cd app && php bin/console doctrine:migrations:migrate --no-interaction"

migrate-test:
	$(DC) exec php sh -lc "cd app && APP_ENV=test php bin/console doctrine:migrations:migrate --no-interaction"

test: migrate-test
	$(DC) exec php sh -lc "cd app && APP_ENV=test php bin/phpunit"
