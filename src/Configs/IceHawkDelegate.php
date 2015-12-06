<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Configs;

use Fortuneglobe\IceHawk\Interfaces\ControlsHandlingBehaviour;
use hollodotme\Readis\TwigPage;

/**
 * Class IceHawkDelegate
 *
 * @package hollodotme\Readis\Configs
 */
final class IceHawkDelegate implements ControlsHandlingBehaviour
{
	public function setUpErrorHandling()
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
	}

	public function setUpSessionHandling()
	{
	}

	public function setUpEnvironment()
	{
	}

	/**
	 * @param \Exception $exception
	 *
	 * @throws \Exception
	 */
	public function handleUncaughtException( \Exception $exception )
	{
		$page = new TwigPage( 'Error.twig', [ 'errorName' => get_class( $exception ), 'error' => $exception ] );
		$page->respond();
	}
}