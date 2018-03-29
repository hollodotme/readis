<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis\DTO;

use hollodotme\Readis\Interfaces\ProvidesKeyInformation;
use Redis;
use function count;

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
	private const TYPES = [
		Redis::REDIS_STRING    => 'string',
		Redis::REDIS_SET       => 'set',
		Redis::REDIS_LIST      => 'list',
		Redis::REDIS_ZSET      => 'zset',
		Redis::REDIS_HASH      => 'hash',
		Redis::REDIS_NOT_FOUND => 'unknown',
	];

	public function __construct( string $name, int $type, float $timeToLive, array $subItems )
	{
		$this->name          = $name;
		$this->type          = self::TYPES[ $type ] ?? 'unknown';
		$this->timeToLive    = $timeToLive;
		$this->subItems      = $subItems;
		$this->countSubItems = count( $subItems );
	}

	public function getName() : string
	{
		return $this->name;
	}

	public function getType() : string
	{
		return $this->type;
	}

	public function getTimeToLive() : float
	{
		return $this->timeToLive;
	}

	public function getSubItems() : array
	{
		return $this->subItems;
	}

	public function countSubItems() : int
	{
		return $this->countSubItems;
	}
}
