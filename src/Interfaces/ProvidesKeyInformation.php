<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Interfaces;

/**
 * Interface ProvidesKeyInformation
 *
 * @package hollodotme\Readis\Interfaces
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
