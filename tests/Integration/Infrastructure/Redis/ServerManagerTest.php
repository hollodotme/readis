<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Infrastructure\Redis;

use Exception;
use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Redis;
use function sprintf;
use function str_repeat;

final class ServerManagerTest extends TestCase
{
	/** @var Redis */
	private $redis;

	protected function setUp() : void
	{
		$this->redis = new Redis();
		$this->redis->connect( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] );
		$this->redis->auth( (string)$_ENV['redis-auth'] );

		$this->redis->slowlog( 'reset' );
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
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\Exception
	 */
	public function testCanConstructFromServerConnection() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		self::assertInstanceOf( ServerManager::class, $serverManager );
	}

	private function getServerConnectionMock( string $host, int $port ) : ProvidesConnectionData
	{
		return new class($host, $port) implements ProvidesConnectionData {
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
				return (string)$_ENV['redis-auth'];
			}
		};
	}

	/**
	 * @throws ConnectionFailedException
	 */
	public function testThrowsExceptionIfConnectionFails() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( (string)$_ENV['redis-host'], 9999 ) );

		$this->expectException( ConnectionFailedException::class );
		$this->expectExceptionMessage(
			sprintf(
				'host: %s, port: 9999, timeout: 2.5, retryInterval: 100, using auth: yes',
				(string)$_ENV['redis-host']
			)
		);

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getKeys();
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 */
	public function testCanGetValueForKey() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		$serverManager->selectDatabase( 0 );

		self::assertSame( 'test', $serverManager->getValue( 'unit' ) );
		self::assertSame( 'test', $serverManager->getValue( 'unit' ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 */
	public function testCanGetServerConfig() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		self::assertNotEmpty( $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws Exception
	 */
	public function testCanGetSlowLogEntries() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		$this->provokeSlowLogEntry();

		$slowLogEntries = $serverManager->getSlowLogEntries();

		self::assertSame( 1, $serverManager->getSlowLogCount() );
		self::assertCount( 1, $slowLogEntries );
		self::assertContainsOnlyInstancesOf( ProvidesSlowLogData::class, $slowLogEntries );

		$slowLogEntry = $slowLogEntries[0];

		self::assertGreaterThanOrEqual( 0, $slowLogEntry->getSlowLogId() );
		self::assertGreaterThan( 0.0, $slowLogEntry->getDuration() );
		self::assertSame( 'FLUSHALL()', $slowLogEntry->getCommand() );
	}

	private function provokeSlowLogEntry() : void
	{
		$this->redis->slowlog( 'reset' );

		for ( $db = 0; $db < 16; $db++ )
		{
			$this->redis->select( $db );
			$this->redis->multi();

			for ( $i = 0; $i < 1000; $i++ )
			{
				$this->redis->set( 'test-' . $i, str_repeat( 'a', 4048 ) );
			}

			/** @noinspection UnusedFunctionResultInspection */
			$this->redis->exec();
		}

		$this->redis->flushAll();
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 */
	public function testCanGetServerInfo() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		self::assertIsArray( $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\Exception
	 */
	public function testCanGetKeyInfoObject() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		$serverManager->selectDatabase( 0 );
		$keyInfo = $serverManager->getKeyInfoObject( 'unit' );

		self::assertSame( 'string', $keyInfo->getType() );
		self::assertSame( -1.0, $keyInfo->getTimeToLive() );
		self::assertCount( 0, $keyInfo->getSubItems() );
		self::assertSame( 'unit', $keyInfo->getName() );
		self::assertSame( 0, $keyInfo->countSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 */
	public function testCanGetKeyInfoObjects() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		$serverManager->selectDatabase( 0 );
		$keyInfos = $serverManager->getKeyInfoObjects( '*', 100 );

		self::assertContainsOnlyInstancesOf( ProvidesKeyInfo::class, $keyInfos );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 */
	public function testCanGetHashValue() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		$hashValue = $serverManager->getHashValue( 'test', 'unit' );

		self::assertSame( '{"json": {"key": "value"}}', $hashValue );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 */
	public function testCanCheckIfCommandExists() : void
	{
		$serverManager = new ServerManager(
			$this->getServerConnectionMock( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] )
		);

		self::assertTrue( $serverManager->commandExists( 'INFO' ) );
		self::assertFalse( $serverManager->commandExists( 'UNKNOWN' ) );
	}
}
