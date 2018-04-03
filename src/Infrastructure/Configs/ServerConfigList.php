<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Configs;

use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;

final class ServerConfigList implements ProvidesServerConfigList
{
	/** @var array|ProvidesServerConfig[] */
	private $servers;

	public function __construct( array $data )
	{
		$this->loadServerConfigs( $data );
	}

	public static function fromConfigFile() : self
	{
		return new self( (array)include __DIR__ . '/../../../config/servers.php' );
	}

	private function loadServerConfigs( array $serverConfigList ) : void
	{
		foreach ( $serverConfigList as $serverConfig )
		{
			$this->servers[] = new ServerConfig(
				$serverConfig['name'],
				$serverConfig['host'],
				(int)$serverConfig['port'],
				(float)$serverConfig['timeout'],
				(int)$serverConfig['retryInterval'],
				$serverConfig['auth'] ?? null,
				$serverConfig['databaseMap'] ?? []
			);
		}
	}

	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs() : array
	{
		return $this->servers;
	}

	/**
	 * @param string $serverKey
	 *
	 * @throws ServerConfigNotFound
	 * @return ProvidesServerConfig
	 */
	public function getServerConfig( string $serverKey ) : ProvidesServerConfig
	{
		if ( isset( $this->servers[ $serverKey ] ) )
		{
			return $this->servers[ $serverKey ];
		}

		throw (new ServerConfigNotFound())->withServerKey( $serverKey );
	}
}
