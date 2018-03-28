<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Interfaces;

/**
 * Interface UnserializesDataToString
 *
 * @package hollodotme\Readis\Interfaces
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
