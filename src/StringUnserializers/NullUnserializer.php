<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\StringUnserializers;

use hollodotme\Readis\Interfaces\UnserializesDataToString;

/**
 * Class NullUnserializer
 *
 * @package hollodotme\Readis\StringUnserializers
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
