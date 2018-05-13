<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\DTO\KeyInfo;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use Redis;
use function array_map;
use function array_slice;

final class ServerManager implements ProvidesRedisData
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
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getServerConfig() : array
	{
		if ( $this->commandExists( 'CONFIG' ) )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			return (array)$this->redis->config( 'GET', '*' );
		}

		return [
			'CONFIG COMMAND IS DISABLED' => 'readis is not able to show the server config.',
		];
	}

	/**
	 * @throws ConnectionFailedException
	 * @return int
	 */
	public function getSlowLogCount() : int
	{
		if ( $this->commandExists( 'SLOWLOG' ) )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			return (int)$this->redis->slowlog( 'len' );
		}

		return 0;
	}

	/**
	 * @param int $limit
	 *
	 * @throws \Exception
	 * @throws ConnectionFailedException
	 * @return array|ProvidesSlowLogData[]
	 */
	public function getSlowLogEntries( int $limit = 100 ) : array
	{
		if ( $this->commandExists( 'SLOWLOG' ) )
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

		return [];
	}

	/**
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getServerInfo() : array
	{
		if ( $this->commandExists( 'INFO' ) )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			return (array)$this->redis->info();
		}

		return [
			'INFO COMMAND IS DISABLED' => 'readis is not able to show the server informtion.',
		];
	}

	/**
	 * @param string $keyPattern
	 *
	 * @throws ConnectionFailedException
	 * @return array
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
	 * @throws ConnectionFailedException
	 * @return array|ProvidesKeyInfo[]
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
	 * @throws ConnectionFailedException
	 * @return ProvidesKeyInfo
	 */
	public function getKeyInfoObject( string $key ) : ProvidesKeyInfo
	{
		/** @noinspection PhpUndefinedMethodInspection */
		[$type, $ttl] = $this->redis->multi()->type( $key )->pttl( $key )->exec();

		if ( $type === Redis::REDIS_HASH )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$subItems = $this->redis->hKeys( $key );

			return new KeyInfo( $key, $type, $ttl, $subItems );
		}

		if ( $type === Redis::REDIS_LIST )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$listLength = $this->redis->llen( $key );

			/** @noinspection PhpUndefinedMethodInspection */
			$subItems = range( 0, $listLength - 1 );

			return new KeyInfo( $key, $type, $ttl, $subItems );
		}

		if ( $type === Redis::REDIS_SET )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$subItems = range( 0, $this->redis->scard( $key ) - 1 );

			return new KeyInfo( $key, $type, $ttl, $subItems );
		}

		if ( $type === Redis::REDIS_ZSET )
		{
			/** @noinspection PhpUndefinedMethodInspection */
			$setLength = $this->redis->zcard( $key );

			/** @noinspection PhpUndefinedMethodInspection */
			$subItems = array_combine(
				range( 0, $setLength - 1 ),
				array_values( $this->redis->zrange( $key, 0, $setLength - 1, true ) )
			);

			return new KeyInfo( $key, $type, $ttl, $subItems );
		}

		return new KeyInfo( $key, $type, $ttl, [] );
	}

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return string
	 */
	public function getValue( string $key ) : string
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (string)$this->redis->get( $key );
	}

	/**
	 * @param string $key
	 * @param string $hashKey
	 *
	 * @throws ConnectionFailedException
	 * @return string
	 */
	public function getHashValue( string $key, string $hashKey ) : string
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (string)$this->redis->hGet( $key, $hashKey );
	}

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllHashValues( string $key ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->hGetAll( $key );
	}

	/**
	 * @param string $key
	 * @param int    $index
	 *
	 * @throws ConnectionFailedException
	 * @return string
	 */
	public function getListElement( string $key, int $index ) : string
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (string)$this->redis->lindex( $key, $index );
	}

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllListElements( string $key ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$count = $this->redis->llen( $key );

		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->lrange( $key, 0, $count - 1 );
	}

	/**
	 * @param string $key
	 * @param int    $index
	 *
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @return string
	 */
	public function getSetMember( string $key, int $index ) : string
	{
		$members = $this->getAllSetMembers( $key );

		if ( !isset( $members[ $index ] ) )
		{
			throw new RuntimeException( 'Could not find member in set anymore.' );
		}

		return $members[ $index ];
	}

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllSetMembers( string $key ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->smembers( $key );
	}

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllSortedSetMembers( string $key ) : array
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$setLength = $this->redis->zcard( $key );

		/** @noinspection PhpUndefinedMethodInspection */
		return (array)$this->redis->zrange( $key, 0, $setLength - 1, true );
	}

	/**
	 * @param string $command
	 *
	 * @throws ConnectionFailedException
	 * @return bool
	 */
	public function commandExists( string $command ) : bool
	{
		/** @noinspection PhpUndefinedMethodInspection */
		$this->redis->rawCommand( $command );
		/** @noinspection PhpUndefinedMethodInspection */
		$error = $this->redis->getLastError();

		return (false === strpos( (string)$error, 'unknown command' ));
	}
}
