<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Configs\ServerConfigList;
use hollodotme\Readis\Infrastructure\Redis\ServerConnection;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use hollodotme\Readis\Interfaces\ProvidesInfrastructure;
use PHPUnit\Framework\TestCase;
use Redis;

abstract class AbstractQueryHandlerTest extends TestCase
{
	/** @var Redis */
	protected $redis;

	protected function setUp() : void
	{
		$this->redis = new Redis();
		$this->redis->connect( 'localhost', 6379 );

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
	 * @param string $serverKey
	 *
	 * @return ProvidesInfrastructure
	 * @throws ServerConfigNotFound
	 */
	protected function getEnvMock( string $serverKey ) : ProvidesInfrastructure
	{
		$serverConfigList = $this->getServerConfigListMock();
		$serverConfig     = $serverConfigList->getServerConfig(
			$serverKey > count( $serverConfigList->getServerConfigs() ) ? '0' : $serverKey
		);
		$serverConnection = new ServerConnection( $serverConfig );

		$env = $this->getMockBuilder( ProvidesInfrastructure::class )->getMockForAbstractClass();
		$env->method( 'getServerConfigList' )->willReturn( $serverConfigList );
		$env->method( 'getServerManager' )
			->with( $serverConfig )
			->willReturn( new ServerManager( $serverConnection ) );

		/** @var ProvidesInfrastructure $env */
		return $env;
	}

	protected function getServerConfigListMock() : ServerConfigList
	{
		return new ServerConfigList(
			[
				[
					'name'          => 'Test Redis1',
					'host'          => 'localhost',
					'port'          => 6379,
					'timeout'       => 2.5,
					'retryInterval' => 100,
					'auth'          => null,
					'databaseMap'   => [],
				],
				[
					'name'          => 'Test Redis2',
					'host'          => 'localhost',
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
