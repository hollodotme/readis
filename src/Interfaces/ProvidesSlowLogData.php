<?php
/**
 * @author h.woltersdorf
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface ProvidesSlowLogData
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface ProvidesSlowLogData
{
	/**
	 * @return int
	 */
	public function getSlowLogId();

	/**
	 * @return \DateTimeImmutable
	 */
	public function getOccurredOn();

	/**
	 * @return float
	 */
	public function getDuration();

	/**
	 * @return string
	 */
	public function getCommand();
}