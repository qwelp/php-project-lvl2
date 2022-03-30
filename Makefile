install:
	composer install

update:
	composer update

dump-autoload:
	composer dump-autoload

console:
	composer exec --verbose psysh

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src bin tests

lint-fix:
	composer exec --verbose phpcbf -- --standard=PSR12 src tests

test:
	composer exec --verbose phpunit tests

test-coverage:
	composer exec --verbose phpunit tests -- --coverage-clover build/logs/clover.xml

test-html:
	composer exec --verbose phpunit tests -- --coverage-html coverage
