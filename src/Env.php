<?php declare(strict_types=1);

namespace hollodotme\Readis;

use hollodotme\Readis\Application\Configs\AppConfig;
use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Infrastructure\Configs\ServerConfigList;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;
use hollodotme\Readis\Infrastructure\Redis\ServerConnection;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use hollodotme\Readis\Interfaces\ProvidesInfrastructure;

final class Env extends AbstractObjectPool implements ProvidesInfrastructure
{
	public function getAppConfig() : AppConfig
	{
		return $this->getSharedInstance(
			'appConfig',
			function ()
			{
				return AppConfig::fromConfigFile();
			}
		);
	}

	public function getServerConfigList() : ProvidesServerConfigList
	{
		return $this->getSharedInstance(
			'serverConfigList',
			function ()
			{
				return ServerConfigList::fromConfigFile();
			}
		);
	}

	public function getServerManager( ProvidesServerConfig $serverConfig ) : ProvidesRedisData
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

	/**
	 * @param string $serverKey
	 *
	 * @throws Exceptions\ServerConfigNotFound
	 * @return ProvidesRedisData
	 */
	public function getServerManagerForServerKey( string $serverKey ) : ProvidesRedisData
	{
		$serverConfigList = $this->getServerConfigList();
		$serverConfig     = $serverConfigList->getServerConfig( $serverKey );

		return $this->getServerManager( $serverConfig );
	}
}
