<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInformation;
use hollodotme\Readis\Application\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Interfaces\UnserializesDataToString;
use hollodotme\Readis\Infrastructure\Redis\DTO\KeyInfo;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use Redis;
use function array_map;
use function array_slice;

final class ServerManager
{
	/** @var RedisWrapper */
	private $redis;

	public function __construct( ProvidesConnectionData $connectionData )
	{
		$this->redis = new RedisWrapper( $connectionData );
	}

	/**
	 * @param int $database
	 *
	 * @throws ConnectionFailedException
	 */
	public function selectDatabase( int $database ) : void
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->select( $database );
	}

	/**
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getServerConfig() : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->config( 'GET', '*' );
	}

	/**
	 * @return int
	 * @throws ConnectionFailedException
	 */
	public function getSlowLogLength() : int
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (int)$this->redis->slowlog( 'len' );
	}

	/**
	 * @param int $limit
	 *
	 * @return array|ProvidesSlowLogData[]
	 * @throws \Exception
	 * @throws ConnectionFailedException
	 */
	public function getSlowLogs( int $limit = 100 ) : array
	{
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		/** @noinspection PhpUndefinedMethodInspection */
		return array_map(
			function ( array $slowLogData )
			{
				return new SlowLogEntry( $slowLogData );
			},
			(array)$this->redis->slowlog( 'get', $limit )
		);
	}

	/**
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getServerInfo() : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->info();
	}

	/**
	 * @param string $keyPattern
	 *
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getKeys( string $keyPattern = '*' ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->keys( $keyPattern );
	}

	/**
	 * @param string   $keyPattern
	 * @param int|null $limit
	 *
	 * @return array|ProvidesKeyInformation[]
	 * @throws ConnectionFailedException
	 */
	public function getKeyInfoObjects( string $keyPattern, ?int $limit ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$keys = $this->redis->keys( $keyPattern );

		if ( null !== $limit )
		{
			$keys = array_slice( $keys, 0, $limit );
		}

		return array_map( [$this, 'getKeyInfoObject'], $keys );
	}

	/**
	 * @param string $key
	 *
	 * @return ProvidesKeyInformation
	 * @throws ConnectionFailedException
	 */
	public function getKeyInfoObject( string $key ) : ProvidesKeyInformation
	{
		/** @noinspection PhpUndefinedMethodInspection */
		[$type, $ttl] = $this->redis->multi()->type( $key )->pttl( $key )->exec();

		if ( $type === Redis::REDIS_HASH )
		{
			/** @noinspection PhpUndefinedMethodInspection */
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
	 * @throws ConnectionFailedException
	 */
	public function getValue( string $key )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return $this->redis->get( $key );
	}

	/**
	 * @param string                   $key
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 * @throws ConnectionFailedException
	 */
	public function getValueAsUnserializedString( string $key, UnserializesDataToString $unserializer )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$serializer = $this->redis->getOption( Redis::OPT_SERIALIZER );

		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_NONE );

		/** @noinspection PhpUndefinedMethodInspection */
		$value = $this->redis->get( $key );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)$serializer );

		return $unserializedValue;
	}

	/**
	 * @param string                   $key
	 * @param string                   $hashKey
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 * @throws ConnectionFailedException
	 */
	public function getHashValueAsUnserializedString( string $key, string $hashKey, UnserializesDataToString $unserializer )
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$serializer = $this->redis->getOption( Redis::OPT_SERIALIZER );

		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)Redis::SERIALIZER_NONE );

		/** @noinspection PhpUndefinedMethodInspection */
		$value = $this->redis->hGet( $key, $hashKey );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->setOption( Redis::OPT_SERIALIZER, (string)$serializer );

		return $unserializedValue;
	}
}
