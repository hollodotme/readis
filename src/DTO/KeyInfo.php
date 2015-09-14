<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\DTO;

use hollodotme\RedisStatus\Interfaces\ProvidesKeyInformation;

/**
 * Class KeyInfo
 *
 * @package hollodotme\RedisStatus\DTO
 */
final class KeyInfo implements ProvidesKeyInformation
{
	/** @var string */
	private $name;

	/** @var string */
	private $type;

	/** @var float */
	private $timeToLive;

	/** @var array */
	private static $types = [
		\Redis::REDIS_STRING    => 'string',
		\Redis::REDIS_SET       => 'set',
		\Redis::REDIS_LIST      => 'list',
		\Redis::REDIS_ZSET      => 'zset',
		\Redis::REDIS_HASH      => 'hash',
		\Redis::REDIS_NOT_FOUND => 'unknown',
	];

	/**
	 * @param string $name
	 * @param int    $type
	 * @param float  $timeToLive
	 */
	public function __construct( $name, $type, $timeToLive )
	{
		$this->name       = $name;
		$this->type       = isset(self::$types[ $type ]) ? self::$types[ $type ] : 'unknown';
		$this->timeToLive = $timeToLive;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return float
	 */
	public function getTimeToLive()
	{
		return $this->timeToLive;
	}
}