<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Interfaces;

/**
 * Interface ProvidesServerConfig
 *
 * @package hollodotme\Readis\Interfaces
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

	/**
	 * @return array
	 */
	public function getDatabaseMap();
}
