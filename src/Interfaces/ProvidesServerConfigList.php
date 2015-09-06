<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface ProvidesServerConfigList
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface ProvidesServerConfigList
{
	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs();
}