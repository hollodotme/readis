<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\DTO\KeyInfo;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Interfaces\ProvidesKeyInformation;
use hollodotme\Readis\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Interfaces\UnserializesDataToString;
use Redis;
use function array_map;
use function array_slice;

final class ServerManager
{
	/** @var Redis */
	private $redis;

	/**
	 * @param ProvidesConnectionData $connectionData
	 *
	 * @throws ConnectionFailedException
	 */
	public function __construct( ProvidesConnectionData $connectionData )
	{
		$this->redis = new Redis();
		$this->connectToServer( $connectionData );
	}

	/**
	 * @param ProvidesConnectionData $connectionData
	 *
	 * @throws ConnectionFailedException
	 */
	private function connectToServer( ProvidesConnectionData $connectionData ) : void
	{
		$connected = @$this->redis->connect(
			$connectionData->getHost(),
			$connectionData->getPort(),
			$connectionData->getTimeout(),
			'',
			$connectionData->getRetryInterval()
		);

		if ( !$connected )
		{
			throw (new ConnectionFailedException())->withConnectionData( $connectionData );
		}
	}

	public function selectDatabase( int $database ) : void
	{
		$this->redis->select( $database );
	}

	public function getServerConfig() : array
	{
		/** @noinspection PhpParamsInspection */
		return (array)$this->redis->config( 'GET', '*' );
	}

	public function getSlowLogLength() : int
	{
		return (int)$this->redis->slowlog( 'len' );
	}

	/**
	 * @param int $limit
	 *
	 * @return array|ProvidesSlowLogData[]
	 */
	public function getSlowLogs( int $limit = 100 ) : array
	{
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		return array_map(
			function ( array $slowLogData )
			{
				return new SlowLogEntry( $slowLogData );
			},
			(array)$this->redis->slowlog( 'get', $limit )
		);
	}

	public function getServerInfo() : array
	{
		return (array)$this->redis->info();
	}

	public function getKeys( string $keyPattern = '*' ) : array
	{
		return (array)$this->redis->keys( $keyPattern );
	}

	/**
	 * @param string   $keyPattern
	 * @param int|null $limit
	 *
	 * @return array|ProvidesKeyInformation[]
	 */
	public function getKeyInfoObjects( string $keyPattern, ?int $limit ) : array
	{
		$keys = $this->redis->keys( $keyPattern );

		if ( null !== $limit )
		{
			$keys = array_slice( $keys, 0, $limit );
		}

		return array_map( [$this, 'getKeyInfoObject'], $keys );
	}

	public function getKeyInfoObject( string $key ) : ProvidesKeyInformation
	{
		/** @noinspection PhpUndefinedMethodInspection */
		[$type, $ttl] = $this->redis->multi()->type( $key )->pttl( $key )->exec();

		if ( $type === Redis::REDIS_HASH )
		{
			$subItems = $this->redis->hKeys( $key );
		}
		else
		{
			$subItems = [];
		}

		return new KeyInfo( $key, $type, $ttl, $subItems );
	}

	/**
	 * @param string $key
	 *
	 * @return bool|string
	 */
	public function getValue( string $key )
	{
		return $this->redis->get( $key );
	}

	/**
	 * @param string                   $key
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 */
	public function getValueAsUnserializedString( string $key, UnserializesDataToString $unserializer )
	{
		$serializer = $this->redis->getOption( Redis::OPT_SERIALIZER );
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_NONE );

		$value = $this->redis->get( $key );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)$serializer );

		return $unserializedValue;
	}

	/**
	 * @param string                   $key
	 * @param string                   $hashKey
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 */
	public function getHashValueAsUnserializedString( string $key, string $hashKey, UnserializesDataToString $unserializer )
	{
		$serializer = $this->redis->getOption( Redis::OPT_SERIALIZER );
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_NONE );

		$value = $this->redis->hGet( $key, $hashKey );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)$serializer );

		return $unserializedValue;
	}
}
