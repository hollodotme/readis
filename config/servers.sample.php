<?php declare(strict_types=1);

return [
	[
		'name'          => 'Local Redis 1',
		'host'          => 'localhost',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
		'databaseMap'   => [
			'0' => 'Sessions',
			'1' => 'Application data',
			'2' => 'and so on',
			/*
			...
			 */
		],
	],
	/*
	[
		'name'          => 'Local Redis 2',
		'host'          => 'localhost',
		'port'          => 6379,
		'timeout'       => 2.5,
		'retryInterval' => 100,
		'auth'          => null,
	],
	*/
];
