test-php-71: start-redis
	docker-compose run --rm php71 php -d auto_prepend_file=/repo/build/xdebug-filter.php /usr/bin/phpunit -c build/phpunit7.xml --testdox
.PHONY: test-php-71

start-redis:
	docker-compose up -d redis
.PHONY: start-redis