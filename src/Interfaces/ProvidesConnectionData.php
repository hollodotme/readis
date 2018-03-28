<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Interfaces;

/**
 * Interface ProvidesConnectionData
 *
 * @package hollodotme\Readis\Interfaces
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
