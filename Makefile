DC = docker compose

.PHONY: build up down restart logs bash php init install deps migrate seed test cs

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

php:
	$(DC) exec php sh

init:
	$(DC) run --rm composer create-project symfony/skeleton app
	$(DC) run --rm composer sh -lc "cd app && composer require webapp doctrine orm maker security validator" 
	$(DC) run --rm composer sh -lc "cd app && composer require --dev symfony/test-pack"

install:
	$(DC) run --rm composer sh -lc "cd app && composer install"

deps:
	$(DC) run --rm composer sh -lc "cd app && composer require webapp doctrine orm maker security validator"

migrate:
	$(DC) exec php sh -lc "cd app && php bin/console doctrine:migrations:migrate --no-interaction"

seed:
	$(DC) exec php sh -lc "cd app && php bin/console doctrine:migrations:migrate --no-interaction"

test:
	$(DC) exec php sh -lc "cd app && php bin/phpunit"
