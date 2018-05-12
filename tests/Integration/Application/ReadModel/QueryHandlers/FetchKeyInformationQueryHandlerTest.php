<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchKeyInformationQueryHandler;
use hollodotme\Readis\Exceptions\KeyTypeNotImplemented;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class FetchKeyInformationQueryHandlerTest extends AbstractQueryHandlerTest
{
	/**
	 * @param string      $key
	 * @param null|string $hashKey
	 * @param string      $expectedKeyType
	 * @param string      $expectedKeyData
	 * @param string      $expectedRawKeyData
	 * @param bool        $expectedHasScore
	 * @param float|null  $expectedScore
	 *
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws KeyTypeNotImplemented
	 * @throws ServerConfigNotFound
	 * @dataProvider keyInfoProvider
	 */
	public function testCanFetchKeyInformation(
		string $key,
		?string $hashKey,
		string $expectedKeyType,
		string $expectedKeyData,
		string $expectedRawKeyData,
		bool $expectedHasScore,
		?float $expectedScore
	) : void
	{
		$serverKey = '0';

		$query  = new FetchKeyInformationQuery( $serverKey, 0, $key, $hashKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertTrue( $result->succeeded() );
		$this->assertFalse( $result->failed() );

		$keyInfo = $result->getKeyInfo();
		$keyData = $result->getKeyData();

		$this->assertSame( $expectedKeyType, $keyInfo->getType() );
		$this->assertSame( $expectedKeyData, $keyData->getKeyData() );
		$this->assertSame( $expectedRawKeyData, $keyData->getRawKeyData() );
		$this->assertSame( $expectedHasScore, $keyData->hasScore() );
		$this->assertSame( $expectedScore, $keyData->getScore() );
	}

	public function keyInfoProvider() : array
	{
		return [
			[
				'key'                => 'unit',
				'hashKey'            => null,
				'expectedType'       => 'string',
				'expectedKeyData'    => 'test',
				'expectedRawKeyData' => 'test',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'test',
				'hashKey'            => 'unit',
				'expectedType'       => 'hash',
				'expectedKeyData'    => "{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => '{"json": {"key": "value"}}',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
		];
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws KeyTypeNotImplemented
	 */
	public function testResultFailsIfServerConfigNotFound() : void
	{
		$serverKey = '3';
		$key       = 'some-key';
		$hashKey   = null;

		$query  = new FetchKeyInformationQuery( $serverKey, 0, $key, $hashKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame( 'Server config not found for server key: 3', $result->getMessage() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 * @throws KeyTypeNotImplemented
	 */
	public function testResultFailsIfConnectionToServerFailed() : void
	{
		$serverKey = '1';
		$key       = 'some-key';
		$hashKey   = null;

		$query  = new FetchKeyInformationQuery( $serverKey, 0, $key, $hashKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getEnvMock( $serverKey ) ))->handle( $query );

		$this->assertFalse( $result->succeeded() );
		$this->assertTrue( $result->failed() );
		$this->assertSame(
			'Could not connect to redis server: host: localhost, port: 9999, timeout: 2.5, retryInterval: 100, using auth: no',
			$result->getMessage()
		);
	}
}
