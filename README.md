![Build](https://github.com/hollodotme/readis/workflows/Build/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/hollodotme/readis/v/stable)](https://packagist.org/packages/hollodotme/readis) 
[![License](https://poser.pugx.org/hollodotme/readis/license)](https://packagist.org/packages/hollodotme/readis)

# re<sup style="color: #ff0000;">a</sup>dis

A web interface to read data from redis server(s)

## Docker image

**[You can find a ready to run docker image here.](https://hub.docker.com/r/hollodotme/readis/)**

```bash
docker pull hollodotme/readis
```

## Features

 * Setup / Selection for multiple redis servers
 * Mapping database keys to expressive database names
 * Selection of a database inside a redis server
 * Searching for keys (with placeholders) inside a database
 * Listing of found keys with variable limit
 * Basic information about keys
 * Viewing the content of all key types
 * Viewing all elements/members/fields in lists, (sorted) sets and hashes all at once
 * Prettified JSON view, if value is a compact JSON string
 * Listing of slow logs
 * Table with all the current server instance information / stats
 * Table with all the current server configs
 * Real-time server monitor for connected clients and I/O in KB/sec.

## Requirements

 * Webserver (nginx, apache2, etc.)
 * PHP >= 7.1 with phpredis extension
 * [composer](https://getcomposer.org)

## Installation

Assuming you'll install re<sup style="color: #ff0000;">a</sup>dis under `/var/www/readis` on your server.

1. SSH into your webserver.
2. `$ git clone https://github.com/hollodotme/readis.git /var/www/readis`
3. `$ cd /var/www/readis`
4. `$ git checkout v2.0.0`
6. `$ composer install -a --no-dev --no-interaction`
7. `$ cp config/app.sample.php config/app.php`
8. Set up the baseUrl in `config/app.php` (Should be the full HTTP URL with path, e.g. `https://www.example.com/readis/`) 
9. `$ cp config/servers.sample.php config/servers.php`
10. Set up all server instances in `config/servers.php`
11. Set up your webserver VHost with document root `/var/www/readis/public`  

### Sample app configuration 

* File: `config/app.php`

Using re<sup style="color: #ff0000;">a</sup>dis under a separate (sub-)domain:

```php
<?php

return [
	'baseUrl' => 'http://readis.example.com',
];
```

Using re<sup style="color: #ff0000;">a</sup>dis under a path of a domain:

```php
<?php

return [
	'baseUrl' => 'http://www.example.com/readis',
];
```

### Sample server configuration

* File: `config/servers.php`

```php
<?php

return [
	[
		'name'          => 'Local redis server 1',
		'host'          => '127.0.0.1',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
		'databaseMap'   => [
			'0' => 'Sessions',
			'1' => 'Sample Data',
			// ...
		],
	],
	/*
	[
		'name'          => 'Local redis server 2',
		'host'          => '127.0.0.2',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
		'databaseMap'   => [
			'0' => 'Sessions',
			'1' => 'Sample Data',
			// ...
		],
	],
	*/
];
```

You can map the numeric database keys to plain text names. 
Keys that were not mapped will still be displayed as `Database [KEY]`.

**Please note:** If the `CONFIG` command is disabled in your redis server, the database map becomes the fallback 
listing of available databases.

**Regarding auth/password:**  
If your redis server is not started with the `requirepass` option and a password, the value for the `auth` config value
must be `null` (not an empty string or `false`). 

### Sample nginx configuration

```nginx
server {
	listen 80;
	
	# Change the domain name
	server_name www.your-domain.net;

	root /var/www/readis/public;
	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$args;
	}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}
}
```

**That's it.**

## Run locally

In order to run re<sup style="color: #ff0000;">a</sup>dis locally, follow these steps:

1. `$ git clone https://github.com/hollodotme/readis.git`
2. `$ cd readis/`
3. `$ composer update -o`
4. `$ cp config/app.sample.php config/app.php` 
5. `$ cp config/servers.sample.php config/servers.php`
6. `$ php -S 127.0.0.1:8080 -t public/` (starts local webserver)
7. `$ docker-compose up -d redis` (starts redis-server instance on `localhost:6379`)
8. Open: http://127.0.0.1:8080
