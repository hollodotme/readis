<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Interfaces;

/**
 * Interface ProvidesServerConfigList
 *
 * @package hollodotme\Readis\Interfaces
 */
interface ProvidesServerConfigList
{
	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs();
}