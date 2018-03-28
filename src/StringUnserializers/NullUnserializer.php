<?php declare(strict_types=1);

namespace hollodotme\Readis\StringUnserializers;

use hollodotme\Readis\Interfaces\UnserializesDataToString;

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
