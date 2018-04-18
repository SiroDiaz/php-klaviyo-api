vendor/autoload.php:
	composer install --no-interaction --prefer-dist

.PHONY: sniff
sniff:
	vendor\bin\phpcs --standard=PSR2 src -n

.PHONY: test
test:
	vendor\bin\phpunit --verbose
