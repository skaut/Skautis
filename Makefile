phpstan:
	docker-compose run --rm tools vendor/bin/phpstan analyse

test:
	docker-compose run --rm tools vendor/bin/phpunit
