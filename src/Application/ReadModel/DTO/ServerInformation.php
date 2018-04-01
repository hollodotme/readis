<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;

final class ServerInformation
{
	/** @var array */
	private $server;

	/** @var array */
	private $serverConfig;

	/** @var int */
	private $slowLogCount;

	/** @var array|SlowLogEntry[] */
	private $slowLogEntries;

	/** @var array */
	private $serverInfo;

	public function __construct( ProvidesServerConfig $server, array $serverConfig, int $slowLogCount, array $slowLogEntries, array $serverInfo )
	{
		$this->server         = $server;
		$this->serverConfig   = $serverConfig;
		$this->slowLogCount   = $slowLogCount;
		$this->slowLogEntries = $slowLogEntries;
		$this->serverInfo     = $serverInfo;
	}

	public function getServer() : ProvidesServerConfig
	{
		return $this->server;
	}

	public function getServerConfig() : array
	{
		return $this->serverConfig;
	}

	public function getSlowLogCount() : int
	{
		return $this->slowLogCount;
	}

	public function getSlowLogEntries() : array
	{
		return $this->slowLogEntries;
	}

	public function getServerInfo() : array
	{
		return $this->serverInfo;
	}
}
