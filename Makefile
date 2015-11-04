.PHONY: test lint lint-auto-fix test-coverage test-unit test-unit-coverage install

test:
	@./vendor/bin/phpunit

lint:
	@./vendor/bin/phpcs -p --standard=PSR2 --warning-severity=0 src/ tests/

lint-fix:
	@./vendor/bin/phpcbf -p --standard=PSR2 src/ tests/

test-coverage:
	@./vendor/bin/phpunit --coverage-text --coverage-html ./tests/report

test-unit:
	@./vendor/bin/phpunit --testsuite unit

test-unit-coverage:
	@./vendor/bin/phpunit --testsuite unit --coverage-text --coverage-html ./tests/report

install:
	@composer install
