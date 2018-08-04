<?php declare(strict_types=1);

use hollodotme\Readis\Application\ReadModel\Prettifiers\HyperLogLogPrettifier;
use hollodotme\Readis\Application\ReadModel\Prettifiers\JsonPrettifier;

return [
	'baseUrl'     => '',
	'prettifiers' => [
		JsonPrettifier::class,
		HyperLogLogPrettifier::class,
	],
];
