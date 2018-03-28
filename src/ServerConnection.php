<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis;

use hollodotme\Readis\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Interfaces\ProvidesServerConfig;

/**
 * Class ServerConnection
 *
 * @package hollodotme\Readis
 */
final class ServerConnection implements ProvidesConnectionData
{
	/** @var string */
	private $host;

	/** @var int */
	private $port;

	/** @var float */
	private $timeout;

	/** @var int */
	private $retryInterval;

	/** @var string|null */
	private $auth;

	/**
	 * @param ProvidesServerConfig $serverConfig
	 */
	public function __construct( ProvidesServerConfig $serverConfig )
	{
		$this->host          = $serverConfig->getHost();
		$this->port          = $serverConfig->getPort();
		$this->timeout       = $serverConfig->getTimeout();
		$this->retryInterval = $serverConfig->getRetryInterval();
		$this->auth          = $serverConfig->getAuth();
	}

	/**
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * @return int
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * @return float
	 */
	public function getTimeout()
	{
		return $this->timeout;
	}

	/**
	 * @return int
	 */
	public function getRetryInterval()
	{
		return $this->retryInterval;
	}

	/**
	 * @return null|string
	 */
	public function getAuth()
	{
		return $this->auth;
	}
}
