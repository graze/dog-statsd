SHELL = /bin/sh

DOCKER ?= $(shell which docker)
PHP_VER := 7.2
IMAGE := graze/php-alpine:${PHP_VER}-test
VOLUME := /srv
DOCKER_RUN_BASE := ${DOCKER} run --rm -t -v $$(pwd):${VOLUME} -w ${VOLUME}
DOCKER_RUN := ${DOCKER_RUN_BASE} ${IMAGE}
OS = $(shell uname)

PREFER_LOWEST ?=

.PHONY: install composer clean help run
.PHONY: test lint lint-fix test-unit test-integration test-matrix test-coverage test-coverage-html test-coverage-clover

.SILENT: help

# Building

build: ## Install the dependencies
	make 'composer-install --optimize-autoloader --prefer-dist ${PREFER_LOWEST}'

build-update: ## Update the dependencies
	make 'composer-update --optimize-autoloader --prefer-dist ${PREFER_LOWEST}'

ensure-composer-file: # Update the composer file
ifeq (${OS},Darwin)
	sed -E -e 's/"php": "[0-9\.]+"/"php": "${PHP_VER}"/' -i '' composer.json
else
	sed -r -e's/"php": "[0-9\.]+"/"php": "${PHP_VER}"/' -i'' composer.json
endif

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
composer-%: ensure-composer-file
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
	${DOCKER_RUN} vendor/bin/phpunit --colors=always --testsuite unit --stop-on-failure

test-integration: ## Run the integration testsuite
	${MAKE} test-echo
	${DOCKER_RUN_BASE} --link python-echo ${IMAGE} vendor/bin/phpunit --colors=always --testsuite integration
	${MAKE} test-echo-stop

test-matrix-lowest: ## Test all version, with the lowest version
	${MAKE} test-matrix PREFER_LOWEST=--prefer-lowest
	${MAKE} build-update

test-matrix: ## Run the unit tests against multiple targets.
	${MAKE} PHP_VER="5.6" build-update test
	${MAKE} PHP_VER="7.0" build-update test
	${MAKE} PHP_VER="7.1" build-update test
	${MAKE} PHP_VER="7.2" build-update test

test-coverage: ## Run all tests and output coverage to the console.
	${MAKE} test-echo
	${DOCKER_RUN_BASE} --link python-echo ${IMAGE} phpdbg7 -qrr vendor/bin/phpunit --coverage-text
	${MAKE} test-echo-stop

test-coverage-html: ## Run all tests and output coverage to html.
	${MAKE} test-echo
	${DOCKER_RUN_BASE} --link python-echo ${IMAGE} phpdbg7 -qrr vendor/bin/phpunit --coverage-html=./tests/report/html
	${MAKE} test-echo-stop

test-coverage-clover: ## Run all tests and output clover coverage to file.
	${MAKE} test-echo
	${DOCKER_RUN_BASE} --link python-echo ${IMAGE} phpdbg7 -qrr vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover
	${MAKE} test-echo-stop

test-echo: ## Run an echo server
test-echo: test-echo-stop
	${DOCKER} run --rm -t -d --name python-echo -v $$(pwd)/tests/src/echo:${VOLUME} -w ${VOLUME} python:2-alpine python echo.py

test-echo-stop: ## Stop the echo server
	@(${DOCKER} stop python-echo || true)

# Help

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/: ## / - /'
