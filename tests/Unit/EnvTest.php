<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit;

use hollodotme\Readis\Env;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 */
	public function testCanGetAppConfig() : void
	{
		$env = new Env();

		self::assertSame( $env->getAppConfig(), $env->getAppConfig() );
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testCanGetServerConfigList() : void
	{
		$env = new Env();

		self::assertSame( $env->getServerConfigList(), $env->getServerConfigList() );
	}

	/**
	 * @throws ExpectationFailedException
	 */
	public function testCanGetServerManager() : void
	{
		$env = new Env();

		$serverManagerA = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6379 ) );
		$serverManagerB = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6379 ) );
		$serverManagerC = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6389 ) );

		self::assertSame( $serverManagerA, $serverManagerB );
		self::assertNotSame( $serverManagerA, $serverManagerC );
		self::assertNotSame( $serverManagerB, $serverManagerC );
	}

	private function getServerConfigMock( string $host, int $port ) : ProvidesServerConfig
	{
		return new class($host, $port) implements ProvidesServerConfig
		{
			/** @var string */
			private $host;

			/** @var int */
			private $port;

			public function __construct( string $host, int $port )
			{
				$this->host = $host;
				$this->port = $port;
			}

			public function getName() : string
			{
				return 'Test-Server';
			}

			public function getHost() : string
			{
				return $this->host;
			}

			public function getPort() : int
			{
				return $this->port;
			}

			public function getTimeout() : float
			{
				return 2.5;
			}

			public function getRetryInterval() : int
			{
				return 100;
			}

			public function getAuth() : ?string
			{
				return null;
			}

			public function getDatabaseMap() : array
			{
				return [];
			}
		};
	}
}
