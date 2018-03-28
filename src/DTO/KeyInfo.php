<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\DTO;

use hollodotme\Readis\Interfaces\ProvidesKeyInformation;

/**
 * Class KeyInfo
 *
 * @package hollodotme\Readis\DTO
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
	private $subItems;

	/** @var int */
	private $countSubItems;

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
	 * @param array  $subItems
	 */
	public function __construct( $name, $type, $timeToLive, array $subItems )
	{
		$this->name          = $name;
		$this->type          = isset(self::$types[ $type ]) ? self::$types[ $type ] : 'unknown';
		$this->timeToLive    = $timeToLive;
		$this->subItems      = $subItems;
		$this->countSubItems = count( $subItems );
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

	/**
	 * @return array
	 */
	public function getSubItems()
	{
		return $this->subItems;
	}

	/**
	 * @return int
	 */
	public function countSubItems()
	{
		return $this->countSubItems;
	}
}
