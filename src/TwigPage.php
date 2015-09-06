<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus;

/**
 * Class TwigPage
 *
 * @package hollodotme\RedisStatus
 */
final class TwigPage
{
	/** @var string */
	private $template;

	/** @var array */
	private $data;

	/**
	 * @param string $template
	 * @param array  $data
	 */
	public function __construct( $template, array $data )
	{
		$this->template = $template;
		$this->data     = $data;
	}

	public function respond()
	{
		$twig = $this->getTwigInstance();

		header( 'Content-Type: text/html; charset=utf-8', true, 200 );
		echo $twig->render( $this->template, $this->data );
	}

	/**
	 * @return \Twig_Environment
	 */
	private function getTwigInstance()
	{
		$twigLoader      = new \Twig_Loader_Filesystem( [ __DIR__ . '/Pages' ] );
		$twigEnvironment = new \Twig_Environment( $twigLoader );
		$twigEnvironment->addExtension( new \Twig_Extension_Debug() );

		return $twigEnvironment;
	}
}