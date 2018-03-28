<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Exceptions;

use hollodotme\Readis\Interfaces\ProvidesConnectionData;

/**
 * Class CannotConnectToServer
 *
 * @package hollodotme\Readis\Exceptions
 */
final class CannotConnectToServer extends ReadisException
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
