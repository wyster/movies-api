DOCKER=docker
DOCKER_COMPOSE?=docker compose
RUN=$(DOCKER_COMPOSE) run --rm app
ENV_FILE ?= .env.local
-include $(ENV_FILE)

REGISTRY_HOST ?=
REGISTRY_REPOSITORY ?=
REGISTRY_USERNAME ?=
REGISTRY_TOKEN ?=

export REGISTRY_USERNAME REGISTRY_TOKEN

# Docker image references use a registry host without a URL scheme.
REGISTRY_HOST := $(patsubst http://%,%,$(patsubst https://%,%,$(REGISTRY_HOST)))

.DEFAULT_GOAL := help
.PHONY: help tty php-cs-fixer phpunit phpstan publish-docker-image

help:
	@printf 'Available commands:\n'
	@awk 'BEGIN {FS = ":.*##"} /^[a-zA-Z0-9_.-]+:.*##/ {printf "  make %-22s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

tty: ## Open a shell in the app container
	$(RUN) /bin/bash

php-cs-fixer: ## Fix PHP code style
	$(RUN) ./vendor/bin/php-cs-fixer fix

phpunit: ## Run PHPUnit tests
	$(RUN) ./vendor/bin/phpunit

phpstan: ## Run PHPStan analysis
	$(RUN) ./vendor/bin/phpstan analyse

publish-docker-image: ## Build and publish the Docker image
	@test -n "$(REGISTRY_HOST)" || (echo "Set REGISTRY_HOST to your container registry host" >&2; exit 1)
	@test -n "$(REGISTRY_REPOSITORY)" || (echo "Set REGISTRY_REPOSITORY to owner/repository" >&2; exit 1)
	@test -n "$$REGISTRY_USERNAME" || (echo "Set REGISTRY_USERNAME in $(ENV_FILE)" >&2; exit 1)
	@test -n "$$REGISTRY_TOKEN" || (echo "Set REGISTRY_TOKEN in $(ENV_FILE)" >&2; exit 1)
	printf '%s' "$$REGISTRY_TOKEN" | docker login "$(REGISTRY_HOST)" --username "$$REGISTRY_USERNAME" --password-stdin
	docker buildx build \
		--platform linux/arm64 \
		--push \
		--tag "$(REGISTRY_HOST)/$(REGISTRY_REPOSITORY):latest" \
		.
