<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use Exception;
use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Queries\FindKeysInDatabaseQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FindKeysInDatabaseQueryHandler;
use hollodotme\Readis\Exceptions\NoServersConfigured;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use function sprintf;

final class FindKeysInDatabaseQueryHandlerTest extends AbstractQueryHandlerTest
{
	/**
	 * @param string   $serverKey
	 * @param int      $database
	 * @param string   $searchPattern
	 * @param int|null $limit
	 * @param int      $expectedKeyCount
	 *
	 * @throws ExpectationFailedException
	 * @throws NoServersConfigured
	 * @throws ServerConfigNotFound
	 * @throws \PHPUnit\Framework\Exception
	 * @dataProvider keySearchProvider
	 */
	public function testCanFindKeysInDatabase(
		string $serverKey,
		int $database,
		string $searchPattern,
		?int $limit,
		int $expectedKeyCount
	) : void
	{
		$serverManager = $this->getServerManagerMock( $serverKey );

		$query  = new FindKeysInDatabaseQuery( $database, $searchPattern, $limit );
		$result = (new FindKeysInDatabaseQueryHandler( $serverManager ))->handle( $query );

		self::assertTrue( $result->succeeded() );
		self::assertFalse( $result->failed() );
		self::assertCount( $expectedKeyCount, $result->getKeyInfoObjects() );
		self::assertContainsOnlyInstancesOf( ProvidesKeyInfo::class, $result->getKeyInfoObjects() );
	}

	public function keySearchProvider() : array
	{
		return [
			[
				'serverKey'        => '0',
				'database'         => 0,
				'searchPattern'    => '*',
				'limit'            => null,
				'expectedKeyCount' => 7,
			],
			[
				'serverKey'        => '0',
				'database'         => 0,
				'searchPattern'    => '*',
				'limit'            => 1,
				'expectedKeyCount' => 1,
			],
			[
				'serverKey'        => '0',
				'database'         => 0,
				'searchPattern'    => '*as*',
				'limit'            => null,
				'expectedKeyCount' => 1,
			],
			[
				'serverKey'        => '0',
				'database'         => 0,
				'searchPattern'    => '*tri*',
				'limit'            => null,
				'expectedKeyCount' => 1,
			],
			[
				'serverKey'        => '0',
				'database'         => 0,
				'searchPattern'    => '*key*',
				'limit'            => null,
				'expectedKeyCount' => 0,
			],
			[
				'serverKey'        => '0',
				'database'         => 1,
				'searchPattern'    => '*',
				'limit'            => null,
				'expectedKeyCount' => 0,
			],
		];
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws ServerConfigNotFound
	 * @throws Exception
	 */
	public function testResultFailsIfConnectionToServerFailed() : void
	{
		$serverKey     = '1';
		$serverManager = $this->getServerManagerMock( $serverKey );

		$query  = new FindKeysInDatabaseQuery( 0, '*', null );
		$result = (new FindKeysInDatabaseQueryHandler( $serverManager ))->handle( $query );

		self::assertFalse( $result->succeeded() );
		self::assertTrue( $result->failed() );
		self::assertSame(
			sprintf(
				'Could not connect to redis server: host: %s, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no',
				(string)$_ENV['redis-host']
			),
			$result->getMessage()
		);
	}
}
