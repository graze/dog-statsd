.PHONY: test test-coverage test-unit test-unit-coverage install

test:
	@./vendor/bin/phpunit

test-coverage:
	@./vendor/bin/phpunit --coverage-text --coverage-html ./tests/report

test-unit:
	@./vendor/bin/phpunit --testsuite unit

test-unit-coverage:
	@./vendor/bin/phpunit --testsuite unit --coverage-text --coverage-html ./tests/report

install:
	@composer install
