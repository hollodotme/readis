<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Exceptions;

use hollodotme\RedisStatus\Interfaces\ProvidesConnectionData;

/**
 * Class CannotConnectToServer
 *
 * @package hollodotme\RedisStatus\Exceptions
 */
final class CannotConnectToServer extends RedisStatusException
{
	/**
	 * @param ProvidesConnectionData $connectionData
	 *
	 * @return $this
	 */
	public function withConnectionData( ProvidesConnectionData $connectionData )
	{
		$this->message = sprintf(
			'host: %s, port: %s, timeout: %s, retryInterval: %s, using auth: %s',
			$connectionData->getHost(),
			$connectionData->getPort(),
			$connectionData->getTimeout(),
			$connectionData->getRetryInterval(),
			!is_null( $connectionData->getAuth() ) ? 'yes' : 'no'
		);

		return $this;
	}
}