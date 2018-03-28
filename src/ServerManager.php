<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis;

use hollodotme\Readis\DTO\KeyInfo;
use hollodotme\Readis\DTO\SlowLogEntry;
use hollodotme\Readis\Exceptions\CannotConnectToServer;
use hollodotme\Readis\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Interfaces\ProvidesKeyInformation;
use hollodotme\Readis\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Interfaces\UnserializesDataToString;

/**
 * Class ServerManager
 *
 * @package hollodotme\Readis
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
	 * @param int $database
	 */
	public function selectDatabase( $database )
	{
		$this->redis->select( $database );
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
	 * @return array|ProvidesSlowLogData[]
	 */
	public function getSlowLogs( $limit = 100 )
	{
		return array_map(
			function ( array $slowLogData )
			{
				return new SlowLogEntry( $slowLogData );
			},
			$this->redis->slowlog( 'get', $limit )
		);
	}

	/**
	 * @return string
	 */
	public function getServerInfo()
	{

		return $this->redis->info();
	}

	/**
	 * @param string $keyPattern
	 *
	 * @return array
	 */
	public function getKeys( $keyPattern = '*' )
	{
		return $this->redis->keys( $keyPattern );
	}

	/**
	 * @param string   $keyPattern
	 * @param int|null $limit
	 *
	 * @return array|ProvidesKeyInformation[]
	 */
	public function getKeyInfoObjects( $keyPattern, $limit )
	{
		$keys = $this->redis->keys( $keyPattern );

		if ( !is_null( $limit ) )
		{
			$keys = array_slice( $keys, 0, $limit );
		}

		return array_map( [ $this, 'getKeyInfoObject' ], $keys );
	}

	/**
	 * @param string $key
	 *
	 * @return ProvidesKeyInformation
	 */
	public function getKeyInfoObject( $key )
	{
		list($type, $ttl) = $this->redis->multi()->type( $key )->pttl( $key )->exec();

		if ( $type == \Redis::REDIS_HASH )
		{
			$subItems = $this->redis->hKeys( $key );
		}
		else
		{
			$subItems = [ ];
		}

		return new KeyInfo( $key, $type, $ttl, $subItems );
	}

	/**
	 * @param string $key
	 *
	 * @return bool|string
	 */
	public function getValue( $key )
	{
		return $this->redis->get( $key );
	}

	/**
	 * @param string                   $key
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 */
	public function getValueAsUnserializedString( $key, UnserializesDataToString $unserializer )
	{
		$serializer = $this->redis->getOption( \Redis::OPT_SERIALIZER );
		$this->redis->setOption( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );

		$value = $this->redis->get( $key );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		$this->redis->setOption( \Redis::OPT_SERIALIZER, $serializer );

		return $unserializedValue;
	}

	/**
	 * @param string                   $key
	 * @param string                   $hashKey
	 * @param UnserializesDataToString $unserializer
	 *
	 * @return bool|string
	 */
	public function getHashValueAsUnserializedString( $key, $hashKey, UnserializesDataToString $unserializer )
	{
		$serializer = $this->redis->getOption( \Redis::OPT_SERIALIZER );
		$this->redis->setOption( \Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE );

		$value = $this->redis->hGet( $key, $hashKey );

		if ( $value !== false )
		{
			$unserializedValue = $unserializer->unserialize( $value );
		}
		else
		{
			$unserializedValue = false;
		}

		$this->redis->setOption( \Redis::OPT_SERIALIZER, $serializer );

		return $unserializedValue;
	}
}
