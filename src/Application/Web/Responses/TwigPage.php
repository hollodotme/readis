<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Responses;

use DateTimeInterface;
use hollodotme\Readis\Exceptions\RuntimeException;
use IntlDateFormatter;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use function base64_encode;
use function dirname;
use function flush;
use function is_string;

final class TwigPage
{
	/** @var Environment */
	private $renderer;

	public function __construct()
	{
		$this->renderer = $this->getTwigInstance();
	}

	/**
	 * @param string $template
	 * @param array  $data
	 * @param int    $httpCode
	 *
	 * @throws RuntimeException
	 */
	public function respond( string $template, array $data, int $httpCode = 200 ) : void
	{
		try
		{
			header( 'Content-Type: text/html; charset=utf-8', true, $httpCode );
			header( 'Access-Control-Allow-Origin: *' );
			echo $this->renderer->render( $template, $this->getMergedData( $data ) );
			flush();
		}
		catch ( Error $e )
		{
			throw new RuntimeException( $e->getMessage(), $e->getCode(), $e );
		}
	}

	private function getTwigInstance() : Environment
	{
		$twigLoader      = new FilesystemLoader( [dirname( __DIR__ )] );
		$twigEnvironment = new Environment( $twigLoader );
		$twigEnvironment->addExtension( new DebugExtension() );

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
		$twigEnvironment->addFilter( $this->getBase64Encoder( 'base64encode' ) );

		return $twigEnvironment;
	}

	private function getIntlDateFilter( string $name, IntlDateFormatter $formatter ) : TwigFilter
	{
		return new TwigFilter(
			$name,
			static function ( $dateValue ) use ( $formatter )
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

	private function getBase64Encoder( string $name ) : TwigFilter
	{
		return new TwigFilter(
			$name,
			static function ( $value )
			{
				return base64_encode( (string)$value );
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
