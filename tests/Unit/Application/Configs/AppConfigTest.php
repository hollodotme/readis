<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\Configs;

use hollodotme\Readis\Application\Configs\AppConfig;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class AppConfigTest extends TestCase
{
	/**
	 * @param array  $configData
	 * @param string $expectedBaseUrl
	 *
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 *
	 * @dataProvider baseUrlProvider
	 */
	public function testCanGetBaseUrl( array $configData, string $expectedBaseUrl ) : void
	{
		$appConfig = new AppConfig( $configData );

		$this->assertSame( $expectedBaseUrl, $appConfig->getBaseUrl() );
	}

	public function baseUrlProvider() : array
	{
		return [
			[
				'configData'      => [
					'baseUrl' => '',
				],
				'expectedBaseUrl' => '',
			],
			[
				'configData'      => [
					'baseUrl' => '/',
				],
				'expectedBaseUrl' => '',
			],
			[
				'configData'      => [
					'baseUrl' => '/readis/',
				],
				'expectedBaseUrl' => '/readis',
			],
			[
				'configData'      => [
					'baseUrl' => 'https://www.example.com/readis/',
				],
				'expectedBaseUrl' => 'https://www.example.com/readis',
			],
		];
	}

	/**
	 * @param array  $configData
	 * @param string $expectedBaseUri
	 *
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 *
	 * @dataProvider baseUriProvider
	 */
	public function testCanGetBaseUri( array $configData, string $expectedBaseUri ) : void
	{
		$appConfig = new AppConfig( $configData );

		$this->assertSame( $expectedBaseUri, $appConfig->getBaseUri() );
	}

	public function baseUriProvider() : array
	{
		return [
			[
				'configData'      => [
					'baseUrl' => '',
				],
				'expectedBaseUri' => '',
			],
			[
				'configData'      => [
					'baseUrl' => '/',
				],
				'expectedBaseUri' => '',
			],
			[
				'configData'      => [
					'baseUrl' => '/readis/',
				],
				'expectedBaseUri' => '/readis',
			],
			[
				'configData'      => [
					'baseUrl' => 'https://www.example.com/readis/',
				],
				'expectedBaseUri' => '/readis',
			],
		];
	}
}
