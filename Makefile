composer-php71:
	docker-compose run --rm php71 composer update -o -v
.PHONY: composer-php71

composer-php72:
	docker-compose run --rm php72 composer update -o -v
.PHONY: composer-php72

composer-php73:
	docker-compose run --rm php73 composer update -o -v
.PHONY: composer-php73

composer-php74:
	docker-compose run --rm php74 composer update -o -v
.PHONY: composer-php74

composer-php80:
	docker-compose run --rm php80 composer update -o -v
.PHONY: composer-php80

test-php-71: composer-php71 start-redis
	docker-compose run --rm php71 \
	php -d error_reporting=-1 \
	-d auto_prepend_file=/repo/build/xdebug-filter.php \
	/usr/bin/phpunit \
	-c /repo/build/phpunit7.xml \
	--testdox
.PHONY: test-php-71

tests: test-php-71 test-php-72 test-php-73 test-php-74 test-php-80
.PHONY: tests

test-php-72: composer-php72 start-redis
	docker-compose run --rm php72 \
	php -d error_reporting=-1 \
	/usr/bin/phpunit \
	-c /repo/build/phpunit8.xml \
	--testdox
.PHONY: test-php-72

test-php-73: composer-php73 start-redis
	docker-compose run --rm php73 \
	php -d error_reporting=-1 \
	/usr/bin/phpunit \
	-c /repo/build/phpunit9.xml \
	--testdox
.PHONY: test-php-73

test-php-74: composer-php74 start-redis
	docker-compose run --rm php74 \
	php -d error_reporting=-1 \
	/usr/bin/phpunit \
	-c /repo/build/phpunit9.xml \
	--testdox
.PHONY: test-php-74

test-php-80: composer-php80 start-redis
	docker-compose run --rm php80 \
	php -d error_reporting=-1 \
	/usr/bin/phpunit \
	-c /repo/build/phpunit9.xml \
	--testdox
.PHONY: test-php-80

start-redis:
	docker-compose up -d redis
	docker-compose exec redis sh -c "cat /tmp/DemoData.txt | redis-cli -a 'password' -n 1 --pipe"
.PHONY: start-redis