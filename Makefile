PORT ?= 8000

start:
	PHP_CLI_SERVER_WORKERS=5 php -S 0.0.0.0:$(PORT) -t public

install:
	composer install

#test:
	#composer phpunit tests

lint:
	composer exec --verbose phpcs -- --standard=PSR12 src public
stan:
	composer phpstan
##########################################################
# Управление контейнерами с помощью docker compose (dc)
##########################################################
dc-build: ## Сборка docker-образов согласно инструкциям из docker-compose.yml
	docker compose build
dc-up: ## Создание и запуск docker-контейнеров, описанных в docker-compose.yml
	docker compose up
dc-up-d: ## Создание и запуск docker-контейнеров, описанных в docker-compose.yml в detach mode
	docker compose up -d
dc-down: ## Остановка и УДАЛЕНИЕ docker-контейнеров, описанных в docker-compose.yml
	docker compose down
dc-stop: ## Остановка docker-контейнеров, описанных в docker-compose.yml
	docker compose stop
dc-start: ## Запуск docker-контейнеров, описанных в docker-compose.yml
	docker compose start
