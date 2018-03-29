<?php declare(strict_types=1);

namespace hollodotme\Readis;

use hollodotme\Readis\Application\Configs\IceHawkConfig;
use hollodotme\Readis\Infrastructure\Configs\IceHawkDelegate;
use IceHawk\IceHawk\IceHawk;

require __DIR__ . '/../vendor/autoload.php';
$env     = new Env();
$iceHawk = new IceHawk( new IceHawkConfig( $env ), new IceHawkDelegate() );
$iceHawk->init();
$iceHawk->handleRequest();
