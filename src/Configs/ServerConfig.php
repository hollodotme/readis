<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Configs;

use hollodotme\Readis\Interfaces\ProvidesServerConfig;

/**
 * Class ServerConfig
 *
 * @package hollodotme\Readis\Configs
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

	/** @var array */
	private $databaseMap;

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
	public function __construct( $name, $host, $port, $timeout, $retryInterval, $auth, $databaseMap )
	{
		$this->name          = $name;
		$this->host          = $host;
		$this->port          = $port;
		$this->timeout       = $timeout;
		$this->retryInterval = $retryInterval;
		$this->auth          = $auth;
		$this->databaseMap   = $databaseMap;
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

	/**
	 * @return array
	 */
	public function getDatabaseMap()
	{
		return $this->databaseMap;
	}
}
