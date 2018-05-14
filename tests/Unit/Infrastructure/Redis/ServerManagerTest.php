<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
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
	 * @throws RuntimeException
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
	 * @throws RuntimeException
	 */
	public function testGetAllSortedSetMembersThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key = 'unit.test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'zcard' )->with( $key )->willReturn( false );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		$serverManager->getAllSortedSetMembers( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
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
	 * @throws RuntimeException
	 */
	public function testGetListElementThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key          = 'unit.test';
		$elementIndex = 0;
		$element      = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'lindex' )->with( $key, $elementIndex )->willReturn( $element );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find element in list anymore.' );

		$serverManager->getListElement( $key, $elementIndex );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
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
	 * @throws RuntimeException
	 */
	public function testGetAllHashValuesThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key        = 'unit.test';
		$hashFields = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'hGetAll' )->with( $key )->willReturn( $hashFields );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		$serverManager->getAllHashValues( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
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
	 * @throws RuntimeException
	 */
	public function testGetAllListElementsThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key = 'unit.test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'llen' )->with( $key )->willReturn( false );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		$serverManager->getAllListElements( $key );
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
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeString() : void
	{
		$key = 'string';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_STRING, -1.0] );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObject = $serverManager->getKeyInfoObject( $key );

		$this->assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		$this->assertSame( 'string', $keyInfoObject->getType() );
		$this->assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		$this->assertSame( 'string', $keyInfoObject->getName() );
		$this->assertSame( 0, $keyInfoObject->countSubItems() );
		$this->assertSame( [], $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeHash() : void
	{
		$key      = 'hash';
		$hashKeys = ['field', 'unit'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_HASH, -1.0] );
		$redisStub->expects( $this->once() )->method( 'hKeys' )->with( $key )->willReturn( $hashKeys );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObject = $serverManager->getKeyInfoObject( $key );

		$this->assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		$this->assertSame( 'hash', $keyInfoObject->getType() );
		$this->assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		$this->assertSame( 'hash', $keyInfoObject->getName() );
		$this->assertSame( 2, $keyInfoObject->countSubItems() );
		$this->assertSame( $hashKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeList() : void
	{
		$key      = 'list';
		$listKeys = [0, 1];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_LIST, -1.0] );
		$redisStub->expects( $this->once() )->method( 'llen' )->with( $key )->willReturn( 2 );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObject = $serverManager->getKeyInfoObject( $key );

		$this->assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		$this->assertSame( 'list', $keyInfoObject->getType() );
		$this->assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		$this->assertSame( 'list', $keyInfoObject->getName() );
		$this->assertSame( 2, $keyInfoObject->countSubItems() );
		$this->assertSame( $listKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeSet() : void
	{
		$key     = 'set';
		$setKeys = [0, 1];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_SET, -1.0] );
		$redisStub->expects( $this->once() )->method( 'scard' )->with( $key )->willReturn( 2 );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObject = $serverManager->getKeyInfoObject( $key );

		$this->assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		$this->assertSame( 'set', $keyInfoObject->getType() );
		$this->assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		$this->assertSame( 'set', $keyInfoObject->getName() );
		$this->assertSame( 2, $keyInfoObject->countSubItems() );
		$this->assertSame( $setKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeSortedSet() : void
	{
		$key              = 'sorted set';
		$setKeysAndScores = [
			0 => 1.0,
			1 => 2.0,
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_ZSET, -1.0] );
		$redisStub->expects( $this->once() )->method( 'zcard' )->with( $key )->willReturn( 2 );
		$redisStub->expects( $this->once() )->method( 'zrange' )->with( $key )->willReturn( $setKeysAndScores );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObject = $serverManager->getKeyInfoObject( $key );

		$this->assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		$this->assertSame( 'zset', $keyInfoObject->getType() );
		$this->assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		$this->assertSame( 'sorted set', $keyInfoObject->getName() );
		$this->assertSame( 2, $keyInfoObject->countSubItems() );
		$this->assertSame( $setKeysAndScores, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testGetHashValue() : void
	{
		$key       = 'hash';
		$hashField = 'field';
		$hashValue = 'value';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'hGet' )->with( $key, $hashField )->willReturn( $hashValue );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $hashValue, $serverManager->getHashValue( $key, $hashField ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 */
	public function testGetHashValueThrowsExceptionForNotExistingField() : void
	{
		$key       = 'hash';
		$hashField = 'not-existing';
		$hashValue = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'hGet' )->with( $key, $hashField )->willReturn( $hashValue );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find field in hash anymore.' );

		$serverManager->getHashValue( $key, $hashField );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeys() : void
	{
		$pattern = '*';
		$keys    = ['string', 'hash', 'list', 'set', 'sorted set'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'keys' )->with( $pattern )->willReturn( $keys );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $keys, $serverManager->getKeys( $pattern ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetValue() : void
	{
		$key   = 'string';
		$value = 'value';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'get' )->with( $key )->willReturn( $value );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $value, $serverManager->getValue( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 */
	public function testGetValueThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key   = 'not-existing';
		$value = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'get' )->with( $key )->willReturn( $value );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key anymore.' );

		$serverManager->getValue( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testGetKeyInfoObjects() : void
	{
		$pattern = '*';
		$keys    = ['string'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( $this->once() )->method( 'keys' )->with( $pattern )->willReturn( $keys );
		$redisStub->expects( $this->once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( $this->once() )->method( 'type' )->with( $keys[0] )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'pttl' )->with( $keys[0] )->willReturnSelf();
		$redisStub->expects( $this->once() )->method( 'exec' )->willReturn( [Redis::REDIS_STRING, -1.0] );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$keyInfoObjects = $serverManager->getKeyInfoObjects( $pattern, 1 );

		$this->assertCount( 1, $keyInfoObjects );
		$this->assertContainsOnlyInstancesOf( ProvidesKeyInfo::class, $keyInfoObjects );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws RuntimeException
	 */
	public function testGetAllSetMembers() : void
	{
		$key        = 'unit.test';
		$setMembers = [
			0 => 'test',
			1 => 'unit',
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'smembers' )->with( $key )->willReturn( $setMembers );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->assertSame( $setMembers, $serverManager->getAllSetMembers( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 */
	public function testGetAllSetMembersThrowsExceptionIfKeyNotFound() : void
	{
		$key        = 'unit.test';
		$setMembers = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'smembers' )->with( $key )->willReturn( $setMembers );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		$serverManager->getAllSetMembers( $key );
	}
}
