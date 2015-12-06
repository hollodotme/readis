<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Configs;

use hollodotme\Readis\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Interfaces\ProvidesServerConfigList;

/**
 * Class ServersConfig
 *
 * @package hollodotme\Readis\Configs
 */
final class ServersConfig implements ProvidesServerConfigList
{
	/** @var array|ProvidesServerConfig[] */
	private $servers;

	public function __construct()
	{
		$serverConfigList = include(__DIR__ . '/../../config/servers.php');

		$this->loadServerConfigs( $serverConfigList );
	}

	/**
	 * @param array $serverConfigList
	 */
	private function loadServerConfigs( array $serverConfigList )
	{
		foreach ( $serverConfigList as $serverConfig )
		{
			$this->servers[] = new ServerConfig(
				$serverConfig['name'],
				$serverConfig['host'],
				intval( $serverConfig['port'] ),
				floatval( $serverConfig['timeout'] ),
				intval( $serverConfig['retryInterval'] ),
				$serverConfig['auth']
			);
		}
	}

	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs()
	{
		return $this->servers;
	}
}