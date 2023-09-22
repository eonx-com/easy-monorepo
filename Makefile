.DEFAULT_GOAL := help
MAKEFLAGS += --no-print-directory
SCRIPTS_DIR := $(abspath $(dir $(lastword $(MAKEFILE_LIST)))/../scripts)
env ?= local
command ?= /bin/sh

check-all: ## Check codebase with all checkers
	@$(MAKE) --jobs=2 --keep-going --output-sync check-composer check-ecs check-monorepo check-phpstan check-rector test

check-composer: ## Validate composer.json
	composer validate --strict

check-ecs: ## Check App with ECS
	quality/vendor/bin/ecs check --ansi --config=quality/ecs.php --memory-limit=2000M

check-monorepo: ## Check monorepo
	vendor/bin/monorepo-builder validate --ansi

check-phpstan: ## Check App with PHPStan
	quality/vendor/bin/phpstan analyse --error-format symplify --ansi --memory-limit=2000M --configuration=quality/phpstan.neon

check-rector: ## Check App with Rector
	quality/vendor/bin/rector process --ansi --config=quality/rector.php --dry-run

fix-ecs: ## Fix issues found by ECS
	quality/vendor/bin/ecs check --fix --ansi --config=quality/ecs.php --memory-limit=2000M

fix-rector: ## Fix issues found by Rector
	quality/vendor/bin/rector process --ansi --config=quality/rector.php

merge: ## Merge all packages
	vendor/bin/monorepo-builder merge --ansi

propagate: ## Propagate all packages
	vendor/bin/monorepo-builder propagate --ansi

release: ## Release all packages
	bin/monorepo clean-up-packages-vendor-dirs
	vendor/bin/monorepo-builder release

split: ## Split all packages
	vendor/bin/monorepo-builder split --ansi

test: ## Execute the tests
	vendor/bin/phpunit packages --coverage-text

help:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	| sed -n 's/^\(.*\):.*##\(.*\)/$(shell tput setaf 2)  \1  :::  $(shell tput sgr0)\2/p' \
	| column -t -s ':::'
