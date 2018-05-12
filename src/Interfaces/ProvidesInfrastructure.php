<?php declare(strict_types=1);

namespace hollodotme\Readis\Interfaces;

use hollodotme\Readis\Application\Configs\AppConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;

interface ProvidesInfrastructure
{
	public function getAppConfig() : AppConfig;

	public function getServerConfigList() : ProvidesServerConfigList;

	public function getServerManager( ProvidesServerConfig $serverConfig ) : ServerManager;

	public function getServerManagerForServerKey( string $serverKey ) : ServerManager;
}
