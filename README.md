[![Build Status](https://travis-ci.org/hollodotme/readis.svg?branch=master)](https://travis-ci.org/hollodotme/readis)
[![Latest Stable Version](https://poser.pugx.org/hollodotme/readis/v/stable)](https://packagist.org/packages/hollodotme/readis) 
[![License](https://poser.pugx.org/hollodotme/readis/license)](https://packagist.org/packages/hollodotme/readis)

# re<sup style="color: #ff0000;">a</sup>dis

A web interface to read data from redis server(s)

## Features

 * Setup / Selection for multiple redis servers
 * Mapping database keys to expressive database names
 * Selection of a database inside a redis server
 * Searching for keys (with placeholders) inside a database
 * Listing of found keys with variable limit
 * Basic information about keys
 * Viewing the content of keys and hash keys
 * Prettyfied JSON view, if value is a JSON string 
 * Listing of slow logs
 * Table with all the current server instance information / stats
 * Table with all the current server configs
 * Real-time server monitor for connected clients and I/O in KB/sec.

## Requirements

 * Webserver (nginx, apache2, etc.)
 * PHP >= 7.1 with phpredis extension

## Installation

Assuming you'll install re<sup style="color: #ff0000;">a</sup>dis under `/var/www/readis` on your server.

1. SSH into your webserver.
2. `$ git clone https://github.com/hollodotme/readis.git /var/www/readis`
3. `$ cd /var/www/readis`
4. `$ git checkout v1.1.3`
5. `$ sh build/tools/update_tools.sh`
6. `$ php build/tools/composer.phar update -o -v`
7. `$ cp config/app.sample.php config/app.php`
8. Set up the baseUrl in `config/app.php` (Should be the full http-URL with path, **without trailing slash**) 
9. `$ cp config/servers.sample.php config/servers.php`
10. Set up all server instances in `config/servers.php`
11. Set up your webserver having a VHost pointing to `/var/www/readis/public`  

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
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;
	}
}
```

**That's it.**

## Public demo

**[See the public demo on readis.hollo.me](http://readis.hollo.me)**
