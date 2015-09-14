<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Interfaces;

/**
 * Interface UnserializesDataToString
 *
 * @package hollodotme\RedisStatus\Interfaces
 */
interface UnserializesDataToString
{
	/**
	 * @param string $data
	 *
	 * @return string
	 */
	public function unserialize( $data );
}