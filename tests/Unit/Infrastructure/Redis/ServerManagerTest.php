<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Redis;
use ReflectionClass;
use ReflectionException;
use const PHP_EOL;

final class ServerManagerTest extends TestCase
{
	/**
	 * @param MockObject $redisStub
	 *
	 * @throws ReflectionException
	 * @return ServerManager
	 */
	protected function getServerManagerWithRedisStub( MockObject $redisStub ) : ServerManager
	{
		$serverConnectionStub = $this->getServerConnectionStub();
		$serverManager        = new ServerManager( $serverConnectionStub );

		$serverManagerReflection = new ReflectionClass( $serverManager );
		$redisProperty           = $serverManagerReflection->getProperty( 'redis' );
		$redisProperty->setAccessible( true );
		$redisProperty->setValue( $serverManager, $redisStub );

		return $serverManager;
	}

	private function getServerConnectionStub() : ProvidesConnectionData
	{
		return new class implements ProvidesConnectionData
		{
			public function getHost() : string
			{
				return 'localhost';
			}

			public function getPort() : int
			{
				return 3306;
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
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws ConnectionFailedException
	 */
	public function testGetAllSortedSetMembers() : void
	{
		$key       = 'unit.test';
		$sortedSet = [
			'unit' => 1.0,
			'test' => 2.0,
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'zcard' )->with( $key )->willReturn( 2 );
		$redisStub->method( 'zrange' )->with( $key, 0, 1 )->willReturn( $sortedSet );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $sortedSet, $serverManager->getAllSortedSetMembers( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetListElement() : void
	{
		$key          = 'unit.test';
		$elementIndex = 0;
		$element      = 'unit test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'lindex' )->with( $key, $elementIndex )->willReturn( $element );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $element, $serverManager->getListElement( $key, $elementIndex ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetAllHashValues() : void
	{
		$key        = 'unit.test';
		$hashFields = [
			'unit' => 'test',
			'test' => 'unit',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'hGetAll' )->with( $key )->willReturn( $hashFields );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $hashFields, $serverManager->getAllHashValues( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetAllListElements() : void
	{
		$key          = 'unit.test';
		$listElements = [
			0 => 'test',
			1 => 'unit',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'llen' )->with( $key )->willReturn( 2 );
		$redisStub->method( 'lrange' )->with( $key, 0, 1 )->willReturn( $listElements );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $listElements, $serverManager->getAllListElements( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetServerConfig() : void
	{
		$serverConfig = ['config' => 'value'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );
		$redisStub->method( 'config' )->with( 'GET', '*' )->willReturn( $serverConfig );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $serverConfig, $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFallbackServerConfigIfConfigCommandIsDisabled() : void
	{
		$serverConfig = [
			'CONFIG COMMAND IS DISABLED' => 'readis is not able to show the server config.',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $serverConfig, $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCommandExists() : void
	{
		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertTrue( $serverManager->commandExists( 'CONFIG' ) );

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertFalse( $serverManager->commandExists( 'CONFIG' ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetServerInfo() : void
	{
		$serverInfo = ['info' => 'value'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'INFO' )->willReturn( implode( PHP_EOL, $serverInfo ) );
		$redisStub->method( 'getLastError' )->willReturn( false );
		$redisStub->method( 'info' )->willReturn( $serverInfo );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $serverInfo, $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFallbackServerInfoIfInfoCommandIsDisabled() : void
	{
		$serverInfo = [
			'INFO COMMAND IS DISABLED' => 'readis is not able to show the server informtion.',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'INFO' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $serverInfo, $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSlowLogCount() : void
	{
		$slowLogCount = 10;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );
		$redisStub->method( 'slowlog' )->with( 'len' )->willReturn( $slowLogCount );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $slowLogCount, $serverManager->getSlowLogCount() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFallbackSlowLogCountIfSlowlogCommandIsDisabled() : void
	{
		$slowLogCount = 0;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $slowLogCount, $serverManager->getSlowLogCount() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 */
	public function testSelectDatabase() : void
	{
		$database  = 1;
		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'select' )->with( $database );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$serverManager->selectDatabase( $database );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws \hollodotme\Readis\Exceptions\RuntimeException
	 */
	public function testGetSetMember() : void
	{
		$key         = 'unit.test';
		$memberIndex = 1;
		$setMembers  = [
			0 => 'test',
			1 => 'unit',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'smembers' )->with( $key )->willReturn( $setMembers );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( 'unit', $serverManager->getSetMember( $key, $memberIndex ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 */
	public function testGetSetMemberThrowsExceptionIfNoMemberWasFoundAtIndex() : void
	{
		$key         = 'unit.test';
		$memberIndex = 2;
		$setMembers  = [
			0 => 'test',
			1 => 'unit',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'smembers' )->with( $key )->willReturn( $setMembers );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find member in set anymore.' );

		$serverManager->getSetMember( $key, $memberIndex );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetSlowLogEntries() : void
	{
		$slowLogEntries = [
			[
				0 => '123',
				1 => time(),
				2 => 123.4,
				3 => ['FLUSHALL'],
			],
			[
				0 => '456',
				1 => time(),
				2 => 456.7,
				3 => ['CONFIG', 'GET', '*'],
			],
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );
		$redisStub->method( 'slowlog' )->with( 'get', 100 )->willReturn( $slowLogEntries );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertContainsOnlyInstancesOf( SlowLogEntry::class, $serverManager->getSlowLogEntries() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanGetFallbackSlowLogEntriesIfSlowlogCommandIsDisabled() : void
	{
		$slowLogEntries = [];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $slowLogEntries, $serverManager->getSlowLogEntries() );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetKeyInfoObject() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetHashValue() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetKeys() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetValue() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetKeyInfoObjects() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}

	/**
	 * @throws \PHPUnit\Framework\IncompleteTestError
	 */
	public function testGetAllSetMembers() : void
	{
		$this->markTestIncomplete( 'Needs implementation.' );
	}
}
