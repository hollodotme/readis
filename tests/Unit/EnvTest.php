<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit;

use hollodotme\Readis\Env;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class EnvTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetAppConfig() : void
	{
		$env = new Env();

		$this->assertSame( $env->getAppConfig(), $env->getAppConfig() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetServerConfigList() : void
	{
		$env = new Env();

		$this->assertSame( $env->getServerConfigList(), $env->getServerConfigList() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetServerManager() : void
	{
		$env = new Env();

		$serverManagerA = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6379 ) );
		$serverManagerB = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6379 ) );
		$serverManagerC = $env->getServerManager( $this->getServerConfigMock( 'localhost', 6389 ) );

		$this->assertSame( $serverManagerA, $serverManagerB );
		$this->assertNotSame( $serverManagerA, $serverManagerC );
		$this->assertNotSame( $serverManagerB, $serverManagerC );
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
