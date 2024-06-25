.DEFAULT_GOAL := help
MAKEFLAGS += --no-print-directory
projectName = Easy Monorepo v5
enableNotification = yes
.ONESHELL:
.SILENT:
SHELL = /bin/bash

define handleError
	echo ''
	echo '$(shell tput setab 1)$(shell echo $@ | sed 's/./ /g')                                                                              $(shell tput sgr0)'
	echo '$(shell tput setab 1) $(shell tput setaf 7)[ERROR] The recipe $(shell tput bold)$@$(shell tput sgr0)$(shell tput setab 1)$(shell tput setaf 7) was executed with errors. Please check the output above. $(shell tput sgr0)'
	echo '$(shell tput setab 1)$(shell echo $@ | sed 's/./ /g')                                                                              $(shell tput sgr0)'
	echo ''

	if [ -n "${EONX_MAKEFILE_ENABLE_NOTIFICATION}" ] && [[ "$(enableNotification)" == "yes" ]]; then
		notify-send -u critical "$(projectName)" "[ERROR] The recipe <b>$@</b> was executed with errors. Please check the output." &>/dev/null || osascript -e 'display notification "[ERROR] The recipe [$@] was executed with errors. Please check the output." with title "$(projectName)" sound name "Submarine"' &>/dev/null
	fi

	# Exit with non zero exit code to allow handling the error
	exit 1
endef

define handleSuccess
	echo ''
	echo '$(shell tput setab 2)$(shell echo $@ | sed 's/./ /g')                                                     $(shell tput sgr0)'
	echo '$(shell tput setab 2) $(shell tput setaf 0)[OK] The recipe $(shell tput bold)$@$(shell tput sgr0)$(shell tput setab 2)$(shell tput setaf 0) was executed successfully. Cheers! $(shell tput sgr0)'
	echo '$(shell tput setab 2)$(shell echo $@ | sed 's/./ /g')                                                     $(shell tput sgr0)'
	echo ''

	if [ -n "${EONX_MAKEFILE_ENABLE_NOTIFICATION}" ] && [[ "$(enableNotification)" == "yes" ]]; then
		notify-send "$(projectName)" "[OK] The recipe <b>$@</b> was executed successfully. Cheers" &>/dev/null || osascript -e 'display notification "[OK] The recipe [$@] was executed successfully. Cheers" with title "$(projectName)" sound name "Submarine"' &>/dev/null
	fi
endef

define runCommand
	if $1; then
		$(call handleSuccess)
	else
		$(call handleError)
	fi
endef

easy-monorepo: ## Execute EasyMonorepo commands
	$(call runCommand,bin/monorepo $(command))

check-all: ## Check codebase with all checkers
	$(call runCommand,$(MAKE) --jobs=2 --keep-going --output-sync \
		check-composer enableNotification="no"\
		check-ecs enableNotification="no"\
		check-monorepo enableNotification="no"\
		check-phpstan enableNotification="no"\
		check-rector enableNotification="no"\
		check-security enableNotification="no")

check-composer: ## Validate composer.json
	$(call runCommand,composer validate --strict)

check-ecs: ## Check App with ECS
	$(call runCommand,quality/vendor/bin/ecs check --ansi --config=quality/ecs.php --memory-limit=2000M)

check-monorepo: ## Check monorepo
	$(call runCommand,vendor/bin/monorepo-builder validate --ansi)

check-phpstan: ## Check App with PHPStan
	$(call runCommand,quality/vendor/bin/phpstan analyse --error-format symplify --ansi --memory-limit=2000M --configuration=quality/phpstan.neon)

check-rector: ## Check App with Rector
	$(call runCommand,quality/vendor/bin/rector process --ansi --config=quality/rector.php --dry-run)

check-security: ## Check packages for known vulnerabilities
	$(call runCommand,composer audit)

fix-ecs: ## Fix issues found by ECS
	$(call runCommand,quality/vendor/bin/ecs check --fix --ansi --config=quality/ecs.php --memory-limit=2000M)

fix-rector: ## Fix issues found by Rector
	$(call runCommand,quality/vendor/bin/rector process --ansi --config=quality/rector.php)

merge: ## Merge all packages
	$(call runCommand,vendor/bin/monorepo-builder merge --ansi)

propagate: ## Propagate all packages
	$(call runCommand,vendor/bin/monorepo-builder propagate --ansi)

release: ## Release all packages (use `version=<version>` as argument to release it with mentioned version)
	$(call runCommand,bin/monorepo clean-up-packages-vendor-dirs \
	&& vendor/bin/monorepo-builder release $(version))

split: ## Split all packages
	$(call runCommand,vendor/bin/monorepo-builder split --ansi)

test: ## Execute the tests
	$(call runCommand,bash bin/run_tests.sh)

test-package: ## Execute the tests for a package (use `package=<packageName>` as argument to execute it)
	$(call runCommand,vendor/bin/phpunit packages/$(package)/tests)

help:
	echo '  ███████  █████  ███████ ██    ██     ███    ███  ██████  ███    ██  ██████  ██████  ███████ ██████   ██████'
	echo '  ██      ██   ██ ██       ██  ██      ████  ████ ██    ██ ████   ██ ██    ██ ██   ██ ██      ██   ██ ██    ██'
	echo '  █████   ███████ ███████   ████       ██ ████ ██ ██    ██ ██ ██  ██ ██    ██ ██████  █████   ██████  ██    ██'
	echo '  ██      ██   ██      ██    ██        ██  ██  ██ ██    ██ ██  ██ ██ ██    ██ ██   ██ ██      ██      ██    ██'
	echo '  ███████ ██   ██ ███████    ██        ██      ██  ██████  ██   ████  ██████  ██   ██ ███████ ██       ██████'
	echo ''
	echo ''
	echo '  ██████  ██    ██     ███████  ██████  ███    ██ $(shell tput setaf 3)██$(shell tput sgr0)   $(shell tput setaf 2)██$(shell tput sgr0)'
	echo '  ██   ██  ██  ██      ██      ██    ██ ████   ██  $(shell tput setaf 3)██$(shell tput sgr0) $(shell tput setaf 2)██$(shell tput sgr0)'
	echo '  ██████    ████       █████   ██    ██ ██ ██  ██   $(shell tput setaf 3)██$(shell tput sgr0)$(shell tput setaf 2)█$(shell tput sgr0)'
	echo '  ██   ██    ██        ██      ██    ██ ██  ██ ██  $(shell tput setaf 1)██$(shell tput sgr0) $(shell tput setaf 4)██$(shell tput sgr0)'
	echo '  ██████     ██        ███████  ██████  ██   ████ $(shell tput setaf 1)██$(shell tput sgr0)   $(shell tput setaf 4)██$(shell tput sgr0)'
	echo ''
	echo '  It is possible to use shortcuts for recipe, like $(shell tput setaf 3)make c-a$(shell tput sgr0) or $(shell tput setaf 3)make c:a$(shell tput sgr0).'
	echo "  Escape commands with options using double or single quotes, like $(shell tput setaf 3)make e:m 'cl -v'$(shell tput sgr0) or $(shell tput setaf 3)make e:m \"cl -v\"$(shell tput sgr0)."
	echo ''
	grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	| sed -n 's/^\(.*\):.*##\(.*\)/$(shell tput setaf 2)  \1$(shell tput sgr0)  :::\2/p' \
	| sed 's/\[\#yellow\]/$(shell tput setaf 3)/g' \
	| sed 's/\[\#black\]/$(shell tput sgr0)/g' \
	| sort \
	| column -t -s  ':::'

	# Exit with non zero exit code to allow handling the error
	exit 1
.DEFAULT:
	if [[ "$(stopExecution)" == "" ]]; then
		$(eval normalizedShortcut := $(shell echo $@ | sed 's/:/-/g' | sed 's/-/[a-zA-Z0-9_]*-/g' | sed "s/\(.*\)/'  \1.*'/"))
		$(eval foundRecipes := $(shell grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sed -n 's/^\(.*\):.*##\(.*\)/  \1/p' | grep -E $(normalizedShortcut)))
		$(eval foundRecipesCount := $(shell echo $(foundRecipes) | tr ' ' '\n' | wc -l))
		if [[ "$(foundRecipesCount)" == "1" ]]; then
			if [[ "$(foundRecipes)" == "" ]]; then
				echo '$(shell tput setaf 3)Recipe$(shell tput sgr0) $(shell tput setaf 2)$@$(shell tput sgr0) $(shell tput setaf 3)is not found.$(shell tput sgr0)'
				echo ''
				echo 'Showing help.'
				echo ''
				$(MAKE) help
			else
				echo 'Executing recipe $(shell tput setaf 2)$(foundRecipes)$(shell tput sgr0)'
				echo ''

				$(eval parsedCommand := $(shell echo $(MAKECMDGOALS) | sed 's/[a-zA-Z0-9_:-]* //'))

				if [[ "$(foundRecipes)" == "easy-monorepo" ]]; then
					$(eval stopExecution='yes')

					if [[ "$(parsedCommand)" == "$(MAKECMDGOALS)" ]]; then
						$(MAKE) $(foundRecipes)
					else
						$(MAKE) $(foundRecipes) command="$(parsedCommand)"
					fi
				else
					$(MAKE) $(foundRecipes)

					if [[ "$(parsedCommand)" != "$(MAKECMDGOALS)" ]]; then
						$(MAKE) $(parsedCommand)
					fi
				fi
			fi
		else
			echo '$(shell tput setaf 3)Multiple recipes are found.$(shell tput sgr0)'
			echo ''
			echo 'Please use one of the following recipes:'
			grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
				| sed -n 's/^\(.*\):.*##\(.*\)/$(shell tput setaf 2)  \1  :::$(shell tput sgr0)\2/p' \
				| sed 's/\[\#yellow\]/$(shell tput setaf 3)/g' \
				| sed 's/\[\#black\]/$(shell tput sgr0)/g' \
				| grep -E $(normalizedShortcut) \
				| sort \
				| column -t -s ':::'

			# Exit with non zero exit code to allow handling the error
			exit 1
		fi
	fi
