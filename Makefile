.DEFAULT_GOAL := help

ifeq (, $(shell which curl))
curl_cmd := docker run --rm -it -v $(PWD):/host -w /host curlimages/curl
else
curl_cmd := curl
endif

-include .env

.PHONY: build
# Read build arguments from the .env file
build: | vendor ## Build container for production env
	docker build -t app $(shell cat .env | awk '!/^(#|$$)/' | xargs -I {} echo -n "--build-arg {} ") .

.PHONY: up
up: | compose-up vendor ## Spin up the Docker containers

.PHONY: compose-up
compose-up:
	docker compose up -d --build

.PHONY: down
down: ## Stop the Docker containers
	docker compose down

.PHONY: shell
shell: ## Enter the app container
	docker compose exec app /bin/bash

.PHONY: cs
cs: | phpcs.phar ## Test coding style
	docker compose exec app phpcs --standard=PSR1,PSR2 src/

vendor: | composer.phar
	docker compose exec app composer install --prefer-dist

composer.phar:
	$(curl_cmd) -O https://getcomposer.org/download/${COMPOSER_VERSION}/composer.phar
	chmod +x $@

phpcs.phar:
	$(curl_cmd) -OL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar
	chmod +x $@

.PHONY: test
test: ## Run unit tests
	docker compose exec app phpunit --testsuite all

.env:
	cp .env.example $@

.PHONY: help
help: col_width := 8
help:
	@echo
	@echo Usage:
	@echo "  make <target>"
	@echo
	@echo Targets:
	@echo "$$(grep -hE '^\S+:.*##' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' -e 's/^\(.\+\):\(.*\)/\1:\2/' | column -c2 -t -s : | sed 's/^/  /')"
	@echo
