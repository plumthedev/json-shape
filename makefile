PHP_VERSION ?= 84
PHP_SERVICE := php$(PHP_VERSION)

php:
	docker compose run --rm $(PHP_SERVICE) php $(filter-out $@,$(MAKECMDGOALS))

composer:
	docker compose run --rm $(PHP_SERVICE) composer $(filter-out $@,$(MAKECMDGOALS))

%:
	@:
