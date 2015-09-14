<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\StringUnserializers;

use hollodotme\RedisStatus\Interfaces\UnserializesDataToString;

/**
 * Class NullUnserializer
 *
 * @package hollodotme\RedisStatus\StringUnserializers
 */
final class NullUnserializer implements UnserializesDataToString
{
	/**
	 * @param string $data
	 *
	 * @return string
	 */
	public function unserialize( $data )
	{
		return $data;
	}
}