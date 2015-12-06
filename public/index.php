<?php
/**
 * Readis
 *
 * @license MIT
 * @author  hollodotme
 * @link    https://github.com/hollodotme/readis
 */

namespace hollodotme\Readis;

use Fortuneglobe\IceHawk\IceHawk;
use hollodotme\Readis\Configs\IceHawkConfig;
use hollodotme\Readis\Configs\IceHawkDelegate;

require(__DIR__ . '/../vendor/autoload.php');

$iceHawk = new IceHawk( new IceHawkConfig(), new IceHawkDelegate() );
$iceHawk->init();
$iceHawk->handleRequest();