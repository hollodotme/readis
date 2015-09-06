<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface ProvidesServerConfig
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface ProvidesServerConfig
{
	/**
	 * @return string
	 */
	public function getName();

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