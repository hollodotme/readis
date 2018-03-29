<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Configs;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;

final class ServerConfig implements ProvidesServerConfig
{
	/** @var string */
	private $name;

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

	/** @var array */
	private $databaseMap;

	public function __construct( string $name, string $host, int $port, float $timeout, int $retryInterval, ?string $auth, array $databaseMap )
	{
		$this->name          = $name;
		$this->host          = $host;
		$this->port          = $port;
		$this->timeout       = $timeout;
		$this->retryInterval = $retryInterval;
		$this->auth          = $auth;
		$this->databaseMap   = $databaseMap;
	}

	public function getName() : string
	{
		return $this->name;
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

	public function getDatabaseMap() : array
	{
		return $this->databaseMap;
	}
}
