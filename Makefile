SHELL = /bin/sh

DOCKER ?= $(shell which docker)
DOCKER_REPOSITORY := graze/php-alpine:test
VOLUME := /srv
DOCKER_RUN_BASE := ${DOCKER} run --rm -t -v $$(pwd):${VOLUME} -w ${VOLUME}
DOCKER_RUN := docker-compose run --rm test

PREFER_LOWEST ?=

.PHONY: install composer clean help run
.PHONY: test lint lint-fix test-unit test-integration test-matrix test-coverage test-coverage-html test-coverage-clover

.SILENT: help

# Building

build: ## Install the dependencies
	make 'composer-install --optimize-autoloader --prefer-dist ${PREFER_LOWEST}'

build-update: ## Update the dependencies
	make 'composer-update --optimize-autoloader --prefer-dist ${PREFER_LOWEST}'

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
	${DOCKER} run -t --rm \
        -v $$(pwd):/app:delegated \
        -v ~/.composer:/tmp:delegated \
        -v ~/.ssh:/root/.ssh:ro \
        composer --ansi --no-interaction $* $(filter-out $@,$(MAKECMDGOALS))

# Testing

test: ## Run the unit and integration testsuites.
test: lint test-unit test-integration

lint: ## Run phpcs against the code.
	${DOCKER_RUN} vendor/bin/phpcs -p --warning-severity=0 src/ tests/

lint-fix: ## Run phpcsf and fix possible lint errors.
	${DOCKER_RUN} vendor/bin/phpcbf -p src/ tests/

test-unit: ## Run the unit testsuite.
	${DOCKER_RUN} vendor/bin/phpunit --colors=always --testsuite unit

test-integration: ## Run the integration testsuite
	${MAKE} test-echo
	docker-compose run --rm test vendor/bin/phpunit --colors=always --testsuite integration
	${MAKE} test-echo-stop

test-matrix-lowest:
	${MAKE} build-update PREFER_LOWEST=--prefer-lowest
	${MAKE} test-matrix
	${MAKE} build-update

test-matrix: ## Run the unit tests against multiple targets.
	${MAKE} IMAGE="php:5.6-alpine" build-update test
	${MAKE} IMAGE="php:7.0-alpine" build-update test
	${MAKE} IMAGE="php:7.1-alpine" build-update test
	${MAKE} IMAGE="php:7.2-alpine" build-update test

test-coverage: ## Run all tests and output coverage to the console.
	${MAKE} test-echo
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-text
	${MAKE} test-echo-stop

test-coverage-html: ## Run all tests and output coverage to html.
	${MAKE} test-echo
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-html=./tests/report/html
	${MAKE} test-echo-stop

test-coverage-clover: ## Run all tests and output clover coverage to file.
	${MAKE} test-echo
	${DOCKER_RUN} phpdbg7 -qrr vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover
	${MAKE} test-echo-stop

test-echo: ## Run an echo server
	docker-compose up -d echo

test-echo-stop: ## Stop the echo server
	docker-compose stop echo

# Help

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/: ## / - /'
