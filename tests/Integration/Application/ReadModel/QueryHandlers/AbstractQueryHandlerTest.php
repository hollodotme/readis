<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Exceptions\NoServersConfigured;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Configs\ServerConfigList;
use hollodotme\Readis\Infrastructure\Redis\ServerConnection;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use hollodotme\Readis\Interfaces\ProvidesInfrastructure;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use Redis;

abstract class AbstractQueryHandlerTest extends TestCase
{
	/** @var Redis */
	protected $redis;

	protected function setUp() : void
	{
		$this->redis = new Redis();
		$this->redis->connect( (string)$_ENV['redis-host'], (int)$_ENV['redis-port'] );
		$this->redis->auth( (string)$_ENV['redis-auth'] );

		$this->redis->slowlog( 'reset' );
		$this->redis->select( 0 );
		$this->redis->set( 'string', '{"json": {"key": "value"}}' );
		$this->redis->hSet( 'hash', 'field', 'value' );
		$this->redis->hSet( 'hash', 'json', '{"json": {"key": "value"}}' );
		$this->redis->rPush( 'list', 'one', 'two', '{"json": {"key": "value"}}' );
		$this->redis->sAdd( 'set', 'one', 'two', '{"json": {"key": "value"}}' );
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$this->redis->zAdd(
			'sorted set',
			1,
			'one',
			2,
			'two',
			2,
			'two again',
			3,
			'{"json": {"key": "value"}}'
		);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$this->redis->geoAdd(
			'geo',
			13.361389,
			38.115556,
			'Palermo',
			15.087269,
			37.502669,
			'Catania'
		);
		/** @noinspection PhpMethodParametersCountMismatchInspection */
		$this->redis->rawCommand( 'PFADD', 'hyperLogLog', 'a', 'b', 'c', 'd', 'e', 'f' );
	}

	protected function tearDown() : void
	{
		$this->redis->flushAll();
		$this->redis = null;
	}

	/**
	 * @param string $serverKey
	 *
	 * @return ProvidesInfrastructure
	 * @throws ServerConfigNotFound
	 * @throws Exception
	 * @throws RuntimeException
	 * @throws NoServersConfigured
	 */
	protected function getEnvMock( string $serverKey ) : ProvidesInfrastructure
	{
		$serverConfigList = $this->getServerConfigListMock();
		$serverConfig     = $serverConfigList->getServerConfig(
			$serverKey > count( $serverConfigList->getServerConfigs() ) ? '0' : $serverKey
		);

		$env = $this->getMockBuilder( ProvidesInfrastructure::class )->getMockForAbstractClass();
		$env->method( 'getServerConfigList' )->willReturn( $serverConfigList );
		$env->method( 'getServerManager' )
		    ->with( $serverConfig )
		    ->willReturn( $this->getServerManagerMock( $serverKey ) );

		/** @var ProvidesInfrastructure $env */
		return $env;
	}

	/**
	 * @param string $serverKey
	 *
	 * @return ProvidesRedisData
	 * @throws NoServersConfigured
	 * @throws ServerConfigNotFound
	 */
	protected function getServerManagerMock( string $serverKey ) : ProvidesRedisData
	{
		$serverConfigList = $this->getServerConfigListMock();
		$serverConfig     = $serverConfigList->getServerConfig(
			$serverKey > count( $serverConfigList->getServerConfigs() ) ? '0' : $serverKey
		);
		$serverConnection = new ServerConnection( $serverConfig );

		return new ServerManager( $serverConnection );
	}

	protected function getServerConfigListMock() : ServerConfigList
	{
		return new ServerConfigList(
			[
				[
					'name'          => 'Test Redis1',
					'host'          => (string)$_ENV['redis-host'],
					'port'          => (int)$_ENV['redis-port'],
					'timeout'       => 2.5,
					'retryInterval' => 100,
					'auth'          => (string)$_ENV['redis-auth'],
					'databaseMap'   => [],
				],
				[
					'name'          => 'Test Redis2',
					'host'          => (string)$_ENV['redis-host'],
					'port'          => 9999,
					'timeout'       => 2.5,
					'retryInterval' => 100,
					'auth'          => null,
					'databaseMap'   => [],
				],
			]
		);
	}
}
