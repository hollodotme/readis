<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface ProvidesConnectionData
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface ProvidesConnectionData
{
	/**
	 * @return string
	 */
	public function getHost();

	/**
	 * @return int
	 */
	public function getPort();

	/**
	 * @return float
	 */
	public function getTimeout();

	/**
	 * @return int
	 */
	public function getRetryInterval();

	/**
	 * @return string|null
	 */
	public function getAuth();
}