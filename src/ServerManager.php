<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus;

use hollodotme\RedisStatus\Exceptions\CannotConnectToServer;
use hollodotme\RedisStatus\Interfaces\ProvidesConnectionData;

/**
 * Class ServerManager
 *
 * @package hollodotme\RedisStatus
 */
final class ServerManager
{
	/** @var \Redis */
	private $redis;

	/**
	 * @param ProvidesConnectionData $connectionData
	 */
	public function __construct( ProvidesConnectionData $connectionData )
	{
		$this->redis = new \Redis();
		$this->connectToServer( $connectionData );
	}

	/**
	 * @param ProvidesConnectionData $connectionData
	 *
	 * @throws CannotConnectToServer
	 */
	private function connectToServer( ProvidesConnectionData $connectionData )
	{
		$connected = $this->redis->connect(
			$connectionData->getHost(),
			$connectionData->getPort(),
			$connectionData->getTimeout(),
			null,
			$connectionData->getRetryInterval()
		);

		if ( !$connected )
		{
			throw ( new CannotConnectToServer() )->withConnectionData( $connectionData );
		}
	}

	/**
	 * @return array
	 */
	public function getServerConfig()
	{
		return $this->redis->config( 'GET', '*' );
	}

	/**
	 * @return int
	 */
	public function getSlowLogLength()
	{
		return $this->redis->slowlog( 'len' );
	}

	/**
	 * @param int $limit
	 *
	 * @return array
	 */
	public function getSlowLogs( $limit = 100 )
	{
		return $this->redis->slowlog( 'get', $limit );
	}
}