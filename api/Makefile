.PHONY: list
list:
	@echo ""
	@echo "Targets for api:"
	@echo ""
	@echo "  dev          > run dev server"
	@echo "  cs           > code sniffer"
	@echo "  cf           > code sniffer fix"
	@echo "  stan         > phpstan"
	@echo "  trans        > extract translations"

.PHONY: dev
dev:
	php -S localhost:8080 -t public public/index.php

.PHONY: stan
stan:
	php -d memory_limit=1G vendor/bin/phpstan analyse

.PHONY: cs
cs:
	vendor/bin/phpcs

.PHONY: cf
cf:
	vendor/bin/phpcbf

.PHONY: trans
trans:
	php bin/console translation:extract en --format=php --force --domain=messages
	php bin/console translation:extract de --format=php --force --domain=messages

.PHONY: build
ASSETS = config public src templates templates_custom translations .env composer.json composer.lock .env
build:
	if [ -d dist ]; then \
		rm -rf dist; \
	fi
	mkdir -p dist
	cp -r $(ASSETS) dist/
	cd dist && APP_ENV=prod composer install --no-dev --no-interaction --no-progress --optimize-autoloader --classmap-authoritative --no-scripts
	rm dist/composer.*
