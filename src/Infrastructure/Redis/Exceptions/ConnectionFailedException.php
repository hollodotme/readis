<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis\Exceptions;

use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Interfaces\ProvidesConnectionData;

final class ConnectionFailedException extends RuntimeException
{
	public function withConnectionData( ProvidesConnectionData $connectionData ) : self
	{
		$this->message = sprintf(
			'host: %s, port: %s, timeout: %s, retryInterval: %s, using auth: %s',
			$connectionData->getHost(),
			$connectionData->getPort(),
			$connectionData->getTimeout(),
			$connectionData->getRetryInterval(),
			null !== $connectionData->getAuth() ? 'yes' : 'no'
		);

		return $this;
	}
}
