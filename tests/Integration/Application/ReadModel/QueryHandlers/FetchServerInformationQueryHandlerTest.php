<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use Exception;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchServerInformationQueryHandler;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use function sprintf;

final class FetchServerInformationQueryHandlerTest extends AbstractQueryHandlerTest
{
	/**
	 * @throws ServerConfigNotFound
	 * @throws Exception
	 */
	public function testCanFetchServerInformation() : void
	{
		$serverKey     = '0';
		$serverManager = $this->getServerManagerMock( $serverKey );

		$result = (new FetchServerInformationQueryHandler( $serverManager ))->handle();

		self::assertTrue( $result->succeeded() );
		self::assertFalse( $result->failed() );

		self::assertNotEmpty( $result->getServerInformation()->getServerConfig() );
		self::assertNotEmpty( $result->getServerInformation()->getServerInfo() );
		self::assertSame( 0, $result->getServerInformation()->getSlowLogCount() );
		self::assertCount( 0, $result->getServerInformation()->getSlowLogEntries() );
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

		$result = (new FetchServerInformationQueryHandler( $serverManager ))->handle();

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
