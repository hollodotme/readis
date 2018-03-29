<?php declare(strict_types=1);

namespace hollodotme\Readis\Exceptions;

final class ServerConfigNotFound extends RuntimeException
{
	/** @var string */
	private $serverKey;

	public function withServerKey( string $serverKey ) : self
	{
		$this->serverKey = $serverKey;

		return $this;
	}

	public function getServerKey() : string
	{
		return $this->serverKey;
	}
}
