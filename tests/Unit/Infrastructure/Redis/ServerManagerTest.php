<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use PHPUnit\Framework\TestCase;
use Redis;

final class ServerManagerTest extends TestCase
{
	/** @var Redis */
	private $redis;

	protected function setUp() : void
	{
		$this->redis = new Redis();
		$this->redis->connect( 'localhost', 6379 );

		$this->redis->select( 0 );
		$this->redis->set( 'unit', 'test' );
		$this->redis->hSet( 'test', 'unit', '{"json": {"key": "value"}}' );
	}

	protected function tearDown() : void
	{
		$this->redis->flushAll();
		$this->redis = null;
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanConstructFromServerConnection() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$this->assertInstanceOf( ServerManager::class, $serverManager );
	}

	private function getServerConnectionMock( string $host, int $port ) : ProvidesConnectionData
	{
		return new class($host, $port) implements ProvidesConnectionData
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
		};
	}

	public function testThrowsExceptionIfConnectionFails() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 9999 ) );

		$this->expectException( ConnectionFailedException::class );
		$this->expectExceptionMessage( 'host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no' );

		$serverManager->getKeys( '*' );
	}
}
