<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Exceptions;

/**
 * Class ServerConfigNotFound
 *
 * @package hollodotme\Readis\Exceptions
 */
final class ServerConfigNotFound extends ReadisException
{
	/** @var string */
	private $serverKey;

	/**
	 * @param string $serverKey
	 *
	 * @return $this
	 */
	public function withServerKey( $serverKey )
	{
		$this->serverKey = $serverKey;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getServerKey()
	{
		return $this->serverKey;
	}
}
