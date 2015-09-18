<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface ProvidesKeyInformation
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface ProvidesKeyInformation
{
	/**
	 * @return string
	 */
	public function getName();

	/**
	 * @return string
	 */
	public function getType();

	/**
	 * @return float
	 */
	public function getTimeToLive();

	/**
	 * @return array
	 */
	public function getSubItems();

	/**
	 * @return int
	 */
	public function countSubItems();
}