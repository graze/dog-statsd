SHELL = /bin/sh

DOCKER ?= $(shell which docker)
DOCKER_REPOSITORY := graze/dog-statsd
VOLUME := /opt/graze/dog-statsd
VOLUME_MAP := -v $$(pwd):${VOLUME}
DOCKER_RUN := ${DOCKER} run --rm -t ${VOLUME_MAP} ${DOCKER_REPOSITORY}:latest

.PHONY: install composer clean help run
.PHONY: test lint lint-fix test-unit test-integration test-matrix test-coverage test-coverage-html test-coverage-clover

.SILENT: help

# Building

install: ## Download the dependencies then build the image :rocket:.
	make 'composer-install --optimize-autoloader --ignore-platform-reqs'
	${DOCKER} build --tag ${DOCKER_REPOSITORY}:latest .

composer-%: ## Run a composer command, `make "composer-<command> [...]"`.
	${DOCKER} run -t --rm \
        -v $$(pwd):/usr/src/app \
        -v ~/.composer:/root/composer \
        -v ~/.ssh:/root/.ssh:ro \
        graze/composer --ansi --no-interaction $* $(filter-out $@,$(MAKECMDGOALS))

clean: ## Clean up any images.
	${DOCKER} rmi ${DOCKER_REPOSITORY}:latest


# Testing

test: ## Run the unit and integration testsuites.
test: lint test-unit

lint: ## Run phpcs against the code.
	${DOCKER_RUN} vendor/bin/phpcs -p --warning-severity=0 src/ tests/

lint-fix: ## Run phpcsf and fix possible lint errors.
	${DOCKER_RUN} vendor/bin/phpcbf -p src/ tests/

test-unit: ## Run the unit testsuite.
	${DOCKER_RUN} vendor/bin/phpunit --colors=always --testsuite unit

test-matrix: ## Run the unit tests against multiple targets.
	${DOCKER} run --rm -t ${VOLUME_MAP} -w ${VOLUME} php:5.6-cli \
    vendor/bin/phpunit --testsuite unit
	${DOCKER} run --rm -t ${VOLUME_MAP} -w ${VOLUME} php:7.0-cli \
    vendor/bin/phpunit --testsuite unit
	${DOCKER} run --rm -t ${VOLUME_MAP} -w ${VOLUME} diegomarangoni/hhvm:cli \
    vendor/bin/phpunit --testsuite unit

test-coverage: ## Run all tests and output coverage to the console.
	${DOCKER_RUN} vendor/bin/phpunit --coverage-text

test-coverage-html: ## Run all tests and output coverage to html.
	${DOCKER_RUN} vendor/bin/phpunit --coverage-html=./tests/report/html

test-coverage-clover: ## Run all tests and output clover coverage to file.
	${DOCKER_RUN} vendor/bin/phpunit --coverage-clover=./tests/report/coverage.clover


# Help

help: ## Show this help message.
	echo "usage: make [target] ..."
	echo ""
	echo "targets:"
	fgrep --no-filename "##" $(MAKEFILE_LIST) | fgrep --invert-match $$'\t' | sed -e 's/: ## / - /'
