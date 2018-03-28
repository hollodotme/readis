<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis;

/**
 * Class TwigPage
 *
 * @package hollodotme\Readis
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
		echo $twig->render( $this->template, $this->getMergedData() );
	}

	/**
	 * @return \Twig_Environment
	 */
	private function getTwigInstance()
	{
		$twigLoader      = new \Twig_Loader_Filesystem( [ __DIR__ . '/Pages' ] );
		$twigEnvironment = new \Twig_Environment( $twigLoader );
		$twigEnvironment->addExtension( new \Twig_Extension_Debug() );

		$dateFormatter = new \IntlDateFormatter(
			null,
			\IntlDateFormatter::MEDIUM,
			\IntlDateFormatter::NONE
		);

		$dateTimeFormatter = new \IntlDateFormatter(
			null,
			\IntlDateFormatter::MEDIUM,
			\IntlDateFormatter::SHORT
		);

		$twigEnvironment->addFilter( $this->getIntlDateFilter( 'formatDate', $dateFormatter ) );
		$twigEnvironment->addFilter( $this->getIntlDateFilter( 'formatDateTime', $dateTimeFormatter ) );

		return $twigEnvironment;
	}

	/**
	 * @param string             $name
	 * @param \IntlDateFormatter $formatter
	 *
	 * @return \Twig_SimpleFilter
	 */
	private function getIntlDateFilter( $name, \IntlDateFormatter $formatter )
	{
		return new \Twig_SimpleFilter(
			$name,
			function ( $dateValue ) use ( $formatter )
			{
				if ( $dateValue instanceof \DateTimeInterface )
				{
					return $formatter->format( $dateValue->getTimestamp() );
				}
				elseif ( is_string( $dateValue ) )
				{
					return $formatter->format( $dateValue );
				}
				else
				{
					return '';
				}
			}
		);
	}

	/**
	 * @return array
	 */
	private function getMergedData()
	{
		return array_merge(
			[
				'appVersion' => '1.1.0',
			],
			$this->data
		);
	}
}
