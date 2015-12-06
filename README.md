# readis

A web interface to read data from redis server(s)


## Requirements

 * Webserver (nginx, apache2, etc.)
 * PHP >= 5.5 with phpredis extension

## Installation

Assuming you'll install readis under `/var/www/readis` on your server.

1. SSH into your webserver.
2. `$ git clone https://github.com/hollodotme/readis.git /var/www/readis`
3. `$ cd /var/www/readis`
4. `$ sh build/tools/update_tools.sh`
5. `$ php build/tools/composer.phar update -o -v`
6. `$ cp config/app.sample.php config/app.php`
7. Set up the baseUrl in `config/app.php` (Should be the full http-URL with path, **without trailing slash**) 
8. `$ cp config/servers.sample.php config/servers.php`
9. Set up all server instances in `config/servers.php`
10. Set up your webserver having a VHost pointing to `/var/www/readis/public`  
See the following nginx example config:

```
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