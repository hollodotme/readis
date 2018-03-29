<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesServerConfig;
use hollodotme\Readis\Infrastructure\Redis\ServerConnection;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class ServerConnectionTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanConstructFromServerConfig() : void
	{
		$connection = new ServerConnection( $this->getServerConfigMock() );

		$this->assertSame( 'localhost', $connection->getHost() );
		$this->assertSame( 6379, $connection->getPort() );
		$this->assertSame( 2.5, $connection->getTimeout() );
		$this->assertNull( $connection->getAuth() );
		$this->assertSame( 100, $connection->getRetryInterval() );
	}

	private function getServerConfigMock() : ProvidesServerConfig
	{
		return new class implements ProvidesServerConfig
		{
			public function getName() : string
			{
				return 'Test-Server-Config';
			}

			public function getHost() : string
			{
				return 'localhost';
			}

			public function getPort() : int
			{
				return 6379;
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
