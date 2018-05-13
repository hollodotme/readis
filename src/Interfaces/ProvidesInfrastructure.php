<?php declare(strict_types=1);

namespace hollodotme\Readis\Interfaces;

use hollodotme\Readis\Application\Configs\AppConfig;
use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;

interface ProvidesInfrastructure
{
	public function getAppConfig() : AppConfig;

	public function getServerConfigList() : ProvidesServerConfigList;

	public function getServerManager( ProvidesServerConfig $serverConfig ) : ProvidesRedisData;

	public function getServerManagerForServerKey( string $serverKey ) : ProvidesRedisData;
}
