PORT ?= 8000

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

setup:
install:
	composer install

#test:
	#composer phpunit tests

lint:
	composer exec --verbose vendor/bin/phpcs -- --standard=PSR12 src public
