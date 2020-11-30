<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Configs;

use hollodotme\Readis\Exceptions\NoServersConfigured;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Exceptions\ServersConfigNotFound;
use hollodotme\Readis\Infrastructure\Configs\ServerConfigList;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class ServerConfigListTest extends TestCase
{
	/**
	 * @param array  $serverConfigs
	 * @param string $serverKey
	 * @param array  $expectedServerConfig
	 *
	 * @throws ExpectationFailedException
	 * @throws ServerConfigNotFound
	 *
	 * @dataProvider serverConfigsProvider
	 */
	public function testCanGetServerConfigForKey(
		array $serverConfigs,
		string $serverKey,
		array $expectedServerConfig
	) : void
	{
		$serverConfig = (new ServerConfigList( $serverConfigs ))->getServerConfig( $serverKey );

		self::assertSame( $expectedServerConfig['name'], $serverConfig->getName() );
		self::assertSame( $expectedServerConfig['host'], $serverConfig->getHost() );
		self::assertSame( $expectedServerConfig['port'], $serverConfig->getPort() );
		self::assertSame( $expectedServerConfig['timeout'], $serverConfig->getTimeout() );
		self::assertSame( $expectedServerConfig['retryInterval'], $serverConfig->getRetryInterval() );
		self::assertSame( $expectedServerConfig['auth'], $serverConfig->getAuth() );
		self::assertSame( $expectedServerConfig['databaseMap'], $serverConfig->getDatabaseMap() );
	}

	public function serverConfigsProvider() : array
	{
		return [
			[
				'serverConfigs'        => [
					[
						'name'          => 'Test Redis1',
						'host'          => 'localhost',
						'port'          => 6379,
						'timeout'       => 2.5,
						'retryInterval' => 100,
						'auth'          => null,
						'databaseMap'   => [],
					],
					[
						'name'          => 'Test Redis 2',
						'host'          => 'localhost',
						'port'          => 6389,
						'timeout'       => 2.5,
						'retryInterval' => 100,
						'auth'          => null,
						'databaseMap'   => [],
					],
				],
				'serverKey'            => '1',
				'expectedServerConfig' => [
					'name'          => 'Test Redis 2',
					'host'          => 'localhost',
					'port'          => 6389,
					'timeout'       => 2.5,
					'retryInterval' => 100,
					'auth'          => null,
					'databaseMap'   => [],
				],
			],
		];
	}

	/**
	 * @param array $serverConfigs
	 *
	 * @throws ExpectationFailedException
	 * @throws NoServersConfigured
	 *
	 * @dataProvider serverConfigsProvider
	 */
	public function testCanGetServerCongigs( array $serverConfigs ) : void
	{
		$serverConfigList = new ServerConfigList( $serverConfigs );

		self::assertContainsOnlyInstancesOf( ProvidesServerConfig::class, $serverConfigList->getServerConfigs() );
	}

	/**
	 * @throws NoServersConfigured
	 */
	public function testThrowsExceptionIfNoServersWereConfigured() : void
	{
		$serverConfigList = new ServerConfigList( [] );

		$this->expectException( NoServersConfigured::class );
		$this->expectExceptionMessage( 'No servers were configured.' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverConfigList->getServerConfigs();
	}

	/**
	 * @param array $serverConfigs
	 *
	 * @throws ServerConfigNotFound
	 * @dataProvider serverConfigsProvider
	 */
	public function testThrowsExceptionForUnknownServerKey( array $serverConfigs ) : void
	{
		$serverConfigList = new ServerConfigList( $serverConfigs );

		$this->expectException( ServerConfigNotFound::class );
		$this->expectExceptionMessage( 'Server config not found for server key: 3' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverConfigList->getServerConfig( '3' );
	}

	/**
	 * @throws ServersConfigNotFound
	 */
	public function testThrowsExceptionIfServersConfigFileNotFound() : void
	{
		$this->expectException( ServersConfigNotFound::class );
		$this->expectExceptionMessage( 'Could not find servers config at /path/to/servers.php' );

		ServerConfigList::fromConfigFile( '/path/to/servers.php' );
	}
}
