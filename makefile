PHP_VERSION ?= 84
PHP_SERVICE := php$(PHP_VERSION)
THROUGH_CONTAINER_CMD := docker compose run --quiet --rm $(PHP_SERVICE)

DOCS_DIR := docs

.PHONY: docs docs-install docs-build

php:
	$(THROUGH_CONTAINER_CMD) php $(filter-out $@,$(MAKECMDGOALS))

composer:
	$(THROUGH_CONTAINER_CMD) composer $(filter-out $@,$(MAKECMDGOALS))

code-style-check:
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/pint --version
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/pint --config=./codequality/pint.json --cache-file=./codequality/.cache/pint/cache.json --parallel --test

code-style-fix:
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/pint --version
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/pint --config=./codequality/pint.json --cache-file=./codequality/.cache/pint/cache.json --parallel --repair

phpstan:
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/phpstan --version
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/phpstan analyse --configuration=./codequality/phpstan.neon --no-progress

phpunit:
	$(THROUGH_CONTAINER_CMD) /var/www/html/vendor/bin/phpunit --configuration=./codequality/phpunit.xml $(filter-out $@,$(MAKECMDGOALS))

docs-install:
	cd $(DOCS_DIR) && npm install

docs-preview: docs-install
	cd $(DOCS_DIR) && npm run docs:dev

docs-build: docs-install
	cd $(DOCS_DIR) && npm run docs:build

%:
	@:
