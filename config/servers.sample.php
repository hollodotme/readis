<?php
/**
 * Sample configuration for redis servers
 *
 * @author hollodotme
 */

return [
	[
		'name'          => 'Local redis server',
		'host'          => 'localhost',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
	]
];