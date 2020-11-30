<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Redis;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Infrastructure\Interfaces\ProvidesConnectionData;
use hollodotme\Readis\Infrastructure\Redis\DTO\SlowLogEntry;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
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
	 * @return ServerManager
	 * @throws ReflectionException
	 */
	private function getServerManagerWithRedisStub( MockObject $redisStub ) : ServerManager
	{
		$serverConnectionStub = $this->getServerConnectionStub();
		$serverManager        = new ServerManager( $serverConnectionStub );

		$redisProperty = (new ReflectionClass( $serverManager ))->getProperty( 'redis' );
		$redisProperty->setAccessible( true );
		$redisProperty->setValue( $serverManager, $redisStub );

		return $serverManager;
	}

	private function getServerConnectionStub() : ProvidesConnectionData
	{
		return new class implements ProvidesConnectionData {
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
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
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

		self::assertSame( $sortedSet, $serverManager->getAllSortedSetMembers( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testGetAllSortedSetMembersThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key = 'unit.test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'zcard' )->with( $key )->willReturn( false );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getAllSortedSetMembers( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testGetListElement() : void
	{
		$key          = 'unit.test';
		$elementIndex = 0;
		$element      = 'unit test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'lindex' )->with( $key, $elementIndex )->willReturn( $element );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $element, $serverManager->getListElement( $key, $elementIndex ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
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

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getListElement( $key, $elementIndex );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws ReflectionException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
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

		self::assertSame( $hashFields, $serverManager->getAllHashValues( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getAllHashValues( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertSame( $listElements, $serverManager->getAllListElements( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetAllListElementsThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key = 'unit.test';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'llen' )->with( $key )->willReturn( false );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key: unit.test' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getAllListElements( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetServerConfig() : void
	{
		$serverConfig = ['config' => 'value'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );
		$redisStub->method( 'config' )->with( 'GET', '*' )->willReturn( $serverConfig );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $serverConfig, $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertSame( $serverConfig, $serverManager->getServerConfig() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 * @throws ReflectionException
	 */
	public function testCommandExists() : void
	{
		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertTrue( $serverManager->commandExists( 'CONFIG' ) );

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'CONFIG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertFalse( $serverManager->commandExists( 'CONFIG' ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetServerInfo() : void
	{
		$serverInfo = ['info' => 'value'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'INFO' )->willReturn( implode( PHP_EOL, $serverInfo ) );
		$redisStub->method( 'getLastError' )->willReturn( false );
		$redisStub->method( 'info' )->willReturn( $serverInfo );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $serverInfo, $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertSame( $serverInfo, $serverManager->getServerInfo() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetSlowLogCount() : void
	{
		$slowLogCount = 10;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'Wrong number of arguments' );
		$redisStub->method( 'slowlog' )->with( 'len' )->willReturn( $slowLogCount );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $slowLogCount, $serverManager->getSlowLogCount() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testCanGetFallbackSlowLogCountIfSlowlogCommandIsDisabled() : void
	{
		$slowLogCount = 0;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $slowLogCount, $serverManager->getSlowLogCount() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testSelectDatabase() : void
	{
		$database  = 1;
		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'select' )->with( $database );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$serverManager->selectDatabase( $database );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertSame( 'unit', $serverManager->getSetMember( $key, $memberIndex ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getSetMember( $key, $memberIndex );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertContainsOnlyInstancesOf( SlowLogEntry::class, $serverManager->getSlowLogEntries() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testCanGetFallbackSlowLogEntriesIfSlowlogCommandIsDisabled() : void
	{
		$slowLogEntries = [];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->method( 'rawCommand' )->with( 'SLOWLOG' )->willReturn( false );
		$redisStub->method( 'getLastError' )->willReturn( 'unknown command' );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $slowLogEntries, $serverManager->getSlowLogEntries() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeString() : void
	{
		$key = 'string';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_STRING, -1.0] );

		$keyInfoObject = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObject( $key );

		/** @noinspection UnnecessaryAssertionInspection */
		self::assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		self::assertSame( 'string', $keyInfoObject->getType() );
		self::assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		self::assertSame( 'string', $keyInfoObject->getName() );
		self::assertSame( 0, $keyInfoObject->countSubItems() );
		self::assertSame( [], $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeHash() : void
	{
		$key      = 'hash';
		$hashKeys = ['field', 'unit'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_HASH, -1.0] );
		$redisStub->expects( self::once() )->method( 'hKeys' )->with( $key )->willReturn( $hashKeys );

		$keyInfoObject = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObject( $key );

		/** @noinspection UnnecessaryAssertionInspection */
		self::assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		self::assertSame( 'hash', $keyInfoObject->getType() );
		self::assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		self::assertSame( 'hash', $keyInfoObject->getName() );
		self::assertSame( 2, $keyInfoObject->countSubItems() );
		self::assertSame( $hashKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeList() : void
	{
		$key      = 'list';
		$listKeys = [0, 1];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_LIST, -1.0] );
		$redisStub->expects( self::once() )->method( 'llen' )->with( $key )->willReturn( 2 );

		$keyInfoObject = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObject( $key );

		/** @noinspection UnnecessaryAssertionInspection */
		self::assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		self::assertSame( 'list', $keyInfoObject->getType() );
		self::assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		self::assertSame( 'list', $keyInfoObject->getName() );
		self::assertSame( 2, $keyInfoObject->countSubItems() );
		self::assertSame( $listKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeSet() : void
	{
		$key     = 'set';
		$setKeys = [0, 1];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_SET, -1.0] );
		$redisStub->expects( self::once() )->method( 'scard' )->with( $key )->willReturn( 2 );

		$keyInfoObject = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObject( $key );

		/** @noinspection UnnecessaryAssertionInspection */
		self::assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		self::assertSame( 'set', $keyInfoObject->getType() );
		self::assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		self::assertSame( 'set', $keyInfoObject->getName() );
		self::assertSame( 2, $keyInfoObject->countSubItems() );
		self::assertSame( $setKeys, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjectForKeysOfTypeSortedSet() : void
	{
		$key              = 'sorted set';
		$setKeysAndScores = [
			0 => 1.0,
			1 => 2.0,
		];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $key )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_ZSET, -1.0] );
		$redisStub->expects( self::once() )->method( 'zcard' )->with( $key )->willReturn( 2 );
		$redisStub->expects( self::once() )->method( 'zrange' )->with( $key )->willReturn( $setKeysAndScores );

		$keyInfoObject = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObject( $key );

		/** @noinspection UnnecessaryAssertionInspection */
		self::assertInstanceOf( ProvidesKeyInfo::class, $keyInfoObject );
		self::assertSame( 'zset', $keyInfoObject->getType() );
		self::assertSame( -1.0, $keyInfoObject->getTimeToLive() );
		self::assertSame( 'sorted set', $keyInfoObject->getName() );
		self::assertSame( 2, $keyInfoObject->countSubItems() );
		self::assertSame( $setKeysAndScores, $keyInfoObject->getSubItems() );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetHashValue() : void
	{
		$key       = 'hash';
		$hashField = 'field';
		$hashValue = 'value';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'hGet' )->with( $key, $hashField )->willReturn( $hashValue );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $hashValue, $serverManager->getHashValue( $key, $hashField ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetHashValueThrowsExceptionForNotExistingField() : void
	{
		$key       = 'hash';
		$hashField = 'not-existing';
		$hashValue = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'hGet' )->with( $key, $hashField )->willReturn( $hashValue );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find field in hash anymore.' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getHashValue( $key, $hashField );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeys() : void
	{
		$pattern = '*';
		$keys    = ['string', 'hash', 'list', 'set', 'sorted set'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'keys' )->with( $pattern )->willReturn( $keys );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $keys, $serverManager->getKeys( $pattern ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetValue() : void
	{
		$key   = 'string';
		$value = 'value';

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'get' )->with( $key )->willReturn( $value );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		self::assertSame( $value, $serverManager->getValue( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetValueThrowsExceptionIfKeyDoesNotExist() : void
	{
		$key   = 'not-existing';
		$value = false;

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'get' )->with( $key )->willReturn( $value );

		$serverManager = $this->getServerManagerWithRedisStub( $redisStub );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key anymore.' );

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getValue( $key );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
	 */
	public function testGetKeyInfoObjects() : void
	{
		$pattern = '*';
		$keys    = ['string'];

		$redisStub = $this->getMockBuilder( Redis::class )->getMock();
		$redisStub->expects( self::once() )->method( 'keys' )->with( $pattern )->willReturn( $keys );
		$redisStub->expects( self::once() )->method( 'multi' )->willReturn( $redisStub );
		$redisStub->expects( self::once() )->method( 'type' )->with( $keys[0] )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'pttl' )->with( $keys[0] )->willReturnSelf();
		$redisStub->expects( self::once() )->method( 'exec' )->willReturn( [Redis::REDIS_STRING, -1.0] );

		$keyInfoObjects = $this->getServerManagerWithRedisStub( $redisStub )->getKeyInfoObjects( $pattern, 1 );

		self::assertCount( 1, $keyInfoObjects );
		self::assertContainsOnlyInstancesOf( ProvidesKeyInfo::class, $keyInfoObjects );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		self::assertSame( $setMembers, $serverManager->getAllSetMembers( $key ) );
	}

	/**
	 * @throws ConnectionFailedException
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 * @throws ReflectionException
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

		/** @noinspection UnusedFunctionResultInspection */
		$serverManager->getAllSetMembers( $key );
	}
}
