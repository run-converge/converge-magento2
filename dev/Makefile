# Run from the dev/ directory.
#
#   make setup        # first-run: download Magento, install, enable module
#   make up           # start the stack
#   make down         # stop the stack (keep data)
#   make destroy      # stop and delete all volumes
#   make magento <cmd># run bin/magento inside the container
#   make sample-data  # deploy Magento sample data (slow)
#   make shell        # open a shell in the phpfpm container
#   make logs         # tail container logs

SHELL := /usr/bin/env bash
DC := docker compose
PHP := $(DC) exec -T phpfpm

.DEFAULT_GOAL := help

.PHONY: help setup up down destroy logs shell sample-data magento ps

help:
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z_-]+:.*## / {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.env:
	@cp .env.example .env
	@echo "Created dev/.env. Add your MAGENTO_PUBLIC_KEY and MAGENTO_PRIVATE_KEY, then re-run make setup."
	@exit 1

setup: .env ## First-run: download Magento, install, enable the module
	$(DC) up -d db opensearch redis
	$(DC) up -d phpfpm
	$(PHP) bash /usr/local/bin/install-magento
	$(DC) up -d nginx
	@echo
	@echo "Open $${MAGENTO_BASE_URL:-http://localhost:8080/}"

up: ## Start the stack
	$(DC) up -d

down: ## Stop the stack (keep data)
	$(DC) down

destroy: ## Stop and delete all volumes
	$(DC) down -v

logs: ## Tail container logs
	$(DC) logs -f

ps: ## Show container status
	$(DC) ps

shell: ## Open a shell in the phpfpm container
	$(DC) exec phpfpm bash

sample-data: ## Deploy Magento sample data (slow)
	$(PHP) bin/magento sampledata:deploy
	$(PHP) bin/magento setup:upgrade
	$(PHP) bin/magento cache:flush

# `make magento cache:flush` -> `bin/magento cache:flush` inside the container.
# The trailing %: target swallows the extra args so make doesn't complain.
magento: ## Run bin/magento <args> inside the container
	$(PHP) bin/magento $(filter-out $@,$(MAKECMDGOALS))

%:
	@:
