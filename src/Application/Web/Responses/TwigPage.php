<?php declare(strict_types=1);

namespace hollodotme\Readis;

use DateTimeInterface;
use hollodotme\Readis\Exceptions\RuntimeException;
use IntlDateFormatter;
use Twig\TwigFilter;
use Twig_Environment;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twig_SimpleFilter;
use function dirname;
use function flush;
use function is_string;

final class TwigPage
{
	/** @var Twig_Environment */
	private $renderer;

	public function __construct()
	{
		$this->renderer = $this->getTwigInstance();
	}

	/**
	 * @param string $template
	 * @param array  $data
	 *
	 * @throws RuntimeException
	 */
	public function respond( string $template, array $data ) : void
	{
		try
		{
			header( 'Content-Type: text/html; charset=utf-8', true, 200 );
			echo $this->renderer->render( $template, $this->getMergedData( $data ) );
			flush();
		}
		catch ( Twig_Error_Loader | Twig_Error_Runtime | Twig_Error_Syntax $e )
		{
			throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	private function getTwigInstance() : Twig_Environment
	{
		$twigLoader      = new Twig_Loader_Filesystem( [dirname( __DIR__ )] );
		$twigEnvironment = new Twig_Environment( $twigLoader );
		$twigEnvironment->addExtension( new Twig_Extension_Debug() );

		$dateFormatter = new IntlDateFormatter(
			'en_GB',
			IntlDateFormatter::MEDIUM,
			IntlDateFormatter::NONE
		);

		$dateTimeFormatter = new IntlDateFormatter(
			'en_GB',
			IntlDateFormatter::MEDIUM,
			IntlDateFormatter::SHORT
		);

		$twigEnvironment->addFilter( $this->getIntlDateFilter( 'formatDate', $dateFormatter ) );
		$twigEnvironment->addFilter( $this->getIntlDateFilter( 'formatDateTime', $dateTimeFormatter ) );

		return $twigEnvironment;
	}

	private function getIntlDateFilter( $name, IntlDateFormatter $formatter ) : Twig_SimpleFilter
	{
		return new TwigFilter(
			$name,
			function ( $dateValue ) use ( $formatter )
			{
				if ( $dateValue instanceof DateTimeInterface )
				{
					return $formatter->format( $dateValue->getTimestamp() );
				}

				if ( is_string( $dateValue ) )
				{
					return $formatter->format( $dateValue );
				}

				return '';
			}
		);
	}

	private function getMergedData( array $data ) : array
	{
		return array_merge(
			[
				'appVersion' => '1.1.0',
			],
			$data
		);
	}
}
