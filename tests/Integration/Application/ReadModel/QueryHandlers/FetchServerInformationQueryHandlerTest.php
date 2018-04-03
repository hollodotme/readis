<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Queries\FetchServerInformationQuery;
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
		$serverKey        = '0';
		$serverConfigList = $this->getServerConfigListMock();

		$query  = new FetchServerInformationQuery( $serverKey );
		$result = (new FetchServerInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertTrue( $result->succeeded() );
		$this->assertFalse( $result->failed() );

		$expectedServerConfig = $serverConfigList->getServerConfig( $serverKey );

		$this->assertEquals( $expectedServerConfig, $result->getServerInformation()->getServer() );
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
	public function testResultFailsIfServerConfigNotFound() : void
	{
		$serverKey = '3';

		$query  = new FetchServerInformationQuery( $serverKey );
		$result = (new FetchServerInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

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

		$query  = new FetchServerInformationQuery( $serverKey );
		$result = (new FetchServerInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame(
			'Could not connect to redis server: host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no',
			$result->getMessage()
		);
	}
}
