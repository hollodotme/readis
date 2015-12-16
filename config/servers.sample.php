<?php
/**
 * Sample configuration for redis servers
 *
 * @author hollodotme
 */

return [
	[
		'name'          => 'Local Redis 1',
		'host'          => 'localhost',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
		],
	//	[
	//		'name'          => 'Local Redis 2',
	//		'host'          => 'localhost',
	//		'port'          => 6380,
	//		'timeout'       => 2.5,
	//		'retryInterval' => 100,
	//		'auth'          => null,
	//	]
];