<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Queries\FindKeysInDatabaseQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FindKeysInDatabaseQueryHandler;
use hollodotme\Readis\Exceptions\NoServersConfigured;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

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
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws NoServersConfigured
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
		$query  = new FindKeysInDatabaseQuery( $serverKey, $database, $searchPattern, $limit );
		$result = (new FindKeysInDatabaseQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertTrue( $result->succeeded() );
		$this->assertFalse( $result->failed() );
		$this->assertCount( $expectedKeyCount, $result->getKeyInfoObjects() );
		$this->assertContainsOnlyInstancesOf( ProvidesKeyInfo::class, $result->getKeyInfoObjects() );
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
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws \Exception
	 */
	public function testResultFailsIfServerConfigNotFound() : void
	{
		$serverKey = '3';

		$query  = new FindKeysInDatabaseQuery( $serverKey, 0, '*', null );
		$result = (new FindKeysInDatabaseQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame( 'Server config not found for server key: 3', $result->getMessage() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws \Exception
	 */
	public function testResultFailsIfConnectionToServerFailed() : void
	{
		$serverKey = '1';

		$query  = new FindKeysInDatabaseQuery( $serverKey, 0, '*', null );
		$result = (new FindKeysInDatabaseQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame(
			'Could not connect to redis server: host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no',
			$result->getMessage()
		);
	}
}
