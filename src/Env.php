<?php declare(strict_types=1);

namespace hollodotme\Readis;

use hollodotme\Readis\Application\Configs\AppConfig;
use hollodotme\Readis\Infrastructure\Configs\ServerConfigList;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;
use hollodotme\Readis\Infrastructure\Redis\ServerConnection;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;

final class Env extends AbstractObjectPool
{
	public function getAppConfig() : AppConfig
	{
		return $this->getSharedInstance(
			'appConfig',
			function ()
			{
				return new AppConfig();
			}
		);
	}

	public function getServerConfigList() : ProvidesServerConfigList
	{
		return $this->getSharedInstance(
			'serverConfigList',
			function ()
			{
				return new ServerConfigList();
			}
		);
	}

	public function getServerManager( ProvidesServerConfig $serverConfig ) : ServerManager
	{
		$name = sprintf( 'serverManager-%s:%d', $serverConfig->getHost(), $serverConfig->getPort() );

		return $this->getSharedInstance(
			$name,
			function () use ( $serverConfig )
			{
				$connection = new ServerConnection( $serverConfig );

				return new ServerManager( $connection );
			}
		);
	}
}
