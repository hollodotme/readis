<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchServerInformationQueryHandler;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class FetchServerInformationQueryHandlerTest extends AbstractQueryHandlerTest
{
	/**
	 * @throws ServerConfigNotFound
	 * @throws \Exception
	 */
	public function testCanFetchServerInformation() : void
	{
		$serverKey     = '0';
		$serverManager = $this->getServerManagerMock( $serverKey );

		$result = (new FetchServerInformationQueryHandler( $serverManager ))->handle();

		$this->assertTrue( $result->succeeded() );
		$this->assertFalse( $result->failed() );

		$this->assertNotEmpty( $result->getServerInformation()->getServerConfig() );
		$this->assertNotEmpty( $result->getServerInformation()->getServerInfo() );
		$this->assertSame( 0, $result->getServerInformation()->getSlowLogCount() );
		$this->assertCount( 0, $result->getServerInformation()->getSlowLogEntries() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws \Exception
	 */
	public function testResultFailsIfConnectionToServerFailed() : void
	{
		$serverKey     = '1';
		$serverManager = $this->getServerManagerMock( $serverKey );

		$result = (new FetchServerInformationQueryHandler( $serverManager ))->handle();

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame(
			'Could not connect to redis server: host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no',
			$result->getMessage()
		);
	}
}
