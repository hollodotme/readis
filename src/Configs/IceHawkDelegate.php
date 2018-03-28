<?php declare(strict_types=1);
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
		$appConfig = new AppConfig();
		$basePath  = parse_url( $appConfig->getBaseUrl(), PHP_URL_PATH );

		$quotedBasePath         = preg_quote( $basePath, '#' );
		$_SERVER['REQUEST_URI'] = preg_replace( "#^{$quotedBasePath}#", '', $_SERVER['REQUEST_URI'] );
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
