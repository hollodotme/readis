<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis\DTO;

use DateTimeImmutable;
use hollodotme\Readis\Application\Interfaces\ProvidesSlowLogData;
use function array_shift;
use function implode;
use function sprintf;
use function strtoupper;

final class SlowLogEntry implements ProvidesSlowLogData
{
	/** @var int */
	private $slowLogId;

	/** @var DateTimeImmutable */
	private $occurredOn;

	/** @var float */
	private $duration;

	/** @var string */
	private $command;

	/**
	 * @param array $slowLogItem
	 *
	 * @throws \Exception
	 */
	public function __construct( array $slowLogItem )
	{
		$this->slowLogId  = $slowLogItem[0];
		$this->occurredOn = new DateTimeImmutable( '@' . $slowLogItem[1] );
		$this->duration   = $slowLogItem[2];
		$this->command    = $this->buildCommandString( $slowLogItem[3] );
	}

	private function buildCommandString( array $arguments ) : string
	{
		$cmd = array_shift( $arguments );

		return sprintf( '%s(%s)', strtoupper( $cmd ), implode( ', ', $arguments ) );
	}

	public function getSlowLogId() : int
	{
		return $this->slowLogId;
	}

	public function getOccurredOn() : DateTimeImmutable
	{
		return $this->occurredOn;
	}

	public function getDuration() : float
	{
		return $this->duration;
	}

	public function getCommand() : string
	{
		return $this->command;
	}
}
