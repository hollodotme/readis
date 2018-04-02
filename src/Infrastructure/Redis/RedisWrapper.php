<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use Redis;

final class RedisWrapper
{
	/** @var ProvidesConnectionData */
	private $connectionData;

	/** @var Redis */
	private $redis;

	/** @var bool */
	private $connected;

	public function __construct( ProvidesConnectionData $connectionData )
	{
		$this->connectionData = $connectionData;
		$this->redis          = new Redis();
	}

	/**
	 * @param string $name
	 * @param array  $arguments
	 *
	 * @return mixed
	 * @throws ConnectionFailedException
	 */
	public function __call( string $name, array $arguments )
	{
		$this->connectToServer();

		return $this->redis->{$name}( ...$arguments );
	}

	/**
	 * @throws ConnectionFailedException
	 */
	private function connectToServer() : void
	{
		if ( $this->connected )
		{
			return;
		}

		$this->connected = @$this->redis->connect(
			$this->connectionData->getHost(),
			$this->connectionData->getPort(),
			$this->connectionData->getTimeout(),
			'',
			$this->connectionData->getRetryInterval()
		);

		if ( !$this->connected )
		{
			throw (new ConnectionFailedException())->withConnectionData( $this->connectionData );
		}

		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_NONE );
		$this->redis->setOption( Redis::OPT_PREFIX, '' );
	}
}
