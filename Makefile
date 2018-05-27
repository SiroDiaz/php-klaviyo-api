vendor/autoload.php:
	composer install --no-interaction --prefer-dist

.PHONY: sniff
sniff:
	vendor\bin\phpcs --standard=PSR2 src -n

.PHONY: cbf
cbf:
	vendor\bin\phpcbf -v -p --standard=PSR2 src

.PHONY: test
test:
	vendor\bin\phpunit --verbose
