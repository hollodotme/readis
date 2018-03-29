<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Redis;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;

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

	public function getHost() : string
	{
		return $this->host;
	}

	public function getPort() : int
	{
		return $this->port;
	}

	public function getTimeout() : float
	{
		return $this->timeout;
	}

	public function getRetryInterval() : int
	{
		return $this->retryInterval;
	}

	public function getAuth() : ?string
	{
		return $this->auth;
	}
}
