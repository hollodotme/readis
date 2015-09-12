<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Configs;

use hollodotme\RedisStatus\Interfaces\ProvidesServerConfig;

/**
 * Class ServerConfig
 *
 * @package hollodotme\RedisStatus\Configs
 */
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

	/**
	 * ServerConfig constructor.
	 *
	 * @param string      $name
	 * @param string      $host
	 * @param int         $port
	 * @param float       $timeout
	 * @param int         $retryInterval
	 * @param null|string $auth
	 */
	public function __construct( $name, $host, $port, $timeout, $retryInterval, $auth )
	{
		$this->name          = $name;
		$this->host          = $host;
		$this->port          = $port;
		$this->timeout       = $timeout;
		$this->retryInterval = $retryInterval;
		$this->auth          = $auth;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
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