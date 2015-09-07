<?php
/**
 * @author h.woltersdorf
 */

namespace hollodotme\RedisStatus\DTO;

use hollodotme\RedisStatus\Interfaces\ProvidesSlowLogData;

/**
 * Class SlowLogEntry
 *
 * @package hollodotme\RedisStatus\DTO
 */
final class SlowLogEntry implements ProvidesSlowLogData
{
	/** @var int */
	private $slowLogId;

	/** @var \DateTimeImmutable */
	private $occurredOn;

	/** @var float */
	private $duration;

	/** @var string */
	private $command;

	public function __construct( array $slowLogItem )
	{
		$this->slowLogId  = $slowLogItem[0];
		$this->occurredOn = new \DateTimeImmutable( '@' . $slowLogItem[1] );
		$this->duration   = $slowLogItem[2];
		$this->command    = $this->buildCommandString( $slowLogItem[3] );
	}

	/**
	 * @param array $arguments
	 *
	 * @return string
	 */
	private function buildCommandString( array $arguments )
	{
		$cmd = array_shift( $arguments );

		return sprintf( '%s(%s)', strtoupper( $cmd ), join( ', ', $arguments ) );
	}

	/**
	 * @return int
	 */
	public function getSlowLogId()
	{
		return $this->slowLogId;
	}

	/**
	 * @return \DateTimeImmutable
	 */
	public function getOccurredOn()
	{
		return $this->occurredOn;
	}

	/**
	 * @return float
	 */
	public function getDuration()
	{
		return $this->duration;
	}

	/**
	 * @return string
	 */
	public function getCommand()
	{
		return $this->command;
	}
}