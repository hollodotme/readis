<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInformation;
use hollodotme\Readis\Application\Interfaces\ProvidesSlowLogData;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Redis;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use function str_repeat;

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
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
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

	/**
	 * @throws ConnectionFailedException
	 */
	public function testThrowsExceptionIfConnectionFails() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 9999 ) );

		$this->expectException( ConnectionFailedException::class );
		$this->expectExceptionMessage( 'host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no' );

		$serverManager->getKeys( '*' );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ConnectionFailedException
	 */
	public function testCanGetValueForKey() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$serverManager->selectDatabase( 0 );

		$this->assertSame( 'test', $serverManager->getValue( 'unit' ) );
		$this->assertSame( 'test', $serverManager->getValue( 'unit' ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetServerConfig() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$this->assertNotEmpty( $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws \Exception
	 */
	public function testCanGetSlowLogEntries() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$this->provokeSlowLogEntry();

		$slowLogEntries = $serverManager->getSlowLogEntries();

		$this->assertSame( 1, $serverManager->getSlowLogCount() );
		$this->assertCount( 1, $slowLogEntries );
		$this->assertContainsOnlyInstancesOf( ProvidesSlowLogData::class, $slowLogEntries );

		$slowLogEntry = $slowLogEntries[0];

		$this->assertGreaterThan( 0, $slowLogEntry->getSlowLogId() );
		$this->assertGreaterThan( 0.0, $slowLogEntry->getDuration() );
		$this->assertSame( 'FLUSHALL()', $slowLogEntry->getCommand() );
	}

	private function provokeSlowLogEntry() : void
	{
		$this->redis->slowlog( 'reset' );

		for ( $db = 0; $db < 16; $db++ )
		{
			$this->redis->select( $db );
			$this->redis->multi();
			$keys = [];

			for ( $i = 0; $i < 1000; $i++ )
			{
				$keys[] = 'test-' . $i;
				$this->redis->set( 'test-' . $i, str_repeat( 'a', 4048 ) );
			}

			$this->redis->exec();
		}

		$this->redis->flushAll();
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetServerInfo() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$this->assertInternalType( 'array', $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetKeyInfoObject() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$serverManager->selectDatabase( 0 );
		$keyInfo = $serverManager->getKeyInfoObject( 'unit' );

		$this->assertSame( 'string', $keyInfo->getType() );
		$this->assertSame( -1.0, $keyInfo->getTimeToLive() );
		$this->assertCount( 0, $keyInfo->getSubItems() );
		$this->assertSame( 'unit', $keyInfo->getName() );
		$this->assertSame( 0, $keyInfo->countSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetKeyInfoObjects() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$serverManager->selectDatabase( 0 );
		$keyInfos = $serverManager->getKeyInfoObjects( '*', 100 );

		$this->assertContainsOnlyInstancesOf( ProvidesKeyInformation::class, $keyInfos );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetHashValue() : void
	{
		$serverManager = new ServerManager( $this->getServerConnectionMock( 'localhost', 6379 ) );

		$hashValue = $serverManager->getHashValue( 'test', 'unit' );

		$this->assertSame( '{"json": {"key": "value"}}', $hashValue );
	}
}
