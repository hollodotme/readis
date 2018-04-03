<?php declare(strict_types=1);

namespace hollodotme\Readis\Exceptions;

final class ServerConfigNotFound extends RuntimeException
{
	public function withServerKey( string $serverKey ) : self
	{
		$this->message = sprintf( 'Server config not found for server key: %s', $serverKey );

		return $this;
	}
}
