target?=src tests

phpstan:
	vendor/bin/phpstan analyse --memory-limit=512M --level 5 $(target)

lint: phpstan

unittest:
	vendor/bin/phpunit tests $(params)

test: unittest
