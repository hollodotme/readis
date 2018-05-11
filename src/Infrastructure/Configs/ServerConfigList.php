<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Configs;

use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Exceptions\ServersConfigNotFound;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfigList;
use function file_exists;

final class ServerConfigList implements ProvidesServerConfigList
{
	/** @var array|ProvidesServerConfig[] */
	private $servers;

	public function __construct( array $data )
	{
		$this->loadServerConfigs( $data );
	}

	/**
	 * @param null|string $configFile
	 *
	 * @throws ServersConfigNotFound
	 * @return ServerConfigList
	 */
	public static function fromConfigFile( ?string $configFile = null ) : self
	{
		$serversConfigFile = $configFile ?? dirname( __DIR__, 3 ) . '/config/servers.php';
		if ( !file_exists( $serversConfigFile ) )
		{
			throw new ServersConfigNotFound( 'Could not find servers config at ' . $serversConfigFile );
		}

		/** @noinspection PhpIncludeInspection */
		return new self( (array)require $serversConfigFile );
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
