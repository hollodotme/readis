<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Interfaces;

use DateTimeImmutable;

interface ProvidesSlowLogData
{
	public function getSlowLogId() : int;

	public function getOccurredOn() : DateTimeImmutable;

	public function getDuration() : float;

	public function getCommand() : string;
}
