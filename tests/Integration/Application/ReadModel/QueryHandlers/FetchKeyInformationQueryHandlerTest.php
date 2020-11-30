<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchKeyInformationQueryHandler;
use hollodotme\Readis\Exceptions\NoServersConfigured;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use PHPUnit\Framework\ExpectationFailedException;
use function preg_quote;

final class FetchKeyInformationQueryHandlerTest extends AbstractQueryHandlerTest
{
	/**
	 * @param string      $key
	 * @param null|string $subKey
	 * @param string      $expectedKeyType
	 * @param string      $expectedKeyData
	 * @param string      $expectedRawKeyData
	 * @param bool        $expectedHasScore
	 * @param float|null  $expectedScore
	 *
	 * @throws ExpectationFailedException
	 * @throws NoServersConfigured
	 * @throws ServerConfigNotFound
	 * @dataProvider keyInfoProvider
	 */
	public function testCanFetchKeyInformation(
		string $key,
		?string $subKey,
		string $expectedKeyType,
		string $expectedKeyData,
		string $expectedRawKeyData,
		bool $expectedHasScore,
		?float $expectedScore
	) : void
	{
		$serverKey = '0';

		$query  = new FetchKeyInformationQuery( 0, $key, $subKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getServerManagerMock( $serverKey ) ))->handle( $query );

		self::assertTrue( $result->succeeded() );
		self::assertFalse( $result->failed() );

		$keyInfo = $result->getKeyInfo();
		$keyData = $result->getKeyData();

		$keyDataPattern    = '#' . preg_quote( $expectedKeyData, '#s' ) . '#';
		$rawKeyDataPattern = '#' . preg_quote( $expectedRawKeyData, '#s' ) . '#';

		self::assertSame( $expectedKeyType, $keyInfo->getType() );
		self::assertTrue( (bool)preg_match( $keyDataPattern, $keyData->getKeyData() ) );
		self::assertTrue( (bool)preg_match( $rawKeyDataPattern, $keyData->getRawKeyData() ) );
		self::assertSame( $expectedHasScore, $keyData->hasScore() );
		self::assertSame( (string)$expectedScore, (string)$keyData->getScore() );
	}

	public function keyInfoProvider() : array
	{
		return [
			[
				'key'                => 'string',
				'subKey'             => null,
				'expectedType'       => 'string',
				'expectedKeyData'    => "{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => '{"json": {"key": "value"}}',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'hash',
				'subKey'             => 'field',
				'expectedType'       => 'hash',
				'expectedKeyData'    => 'value',
				'expectedRawKeyData' => 'value',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'hash',
				'subKey'             => 'json',
				'expectedType'       => 'hash',
				'expectedKeyData'    => "{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => '{"json": {"key": "value"}}',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'hash',
				'subKey'             => null,
				'expectedType'       => 'hash',
				'expectedKeyData'    => "Field field:\n============\nvalue\n\n---\n\nField json:\n===========\n{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => "Field field:\n============\nvalue\n\n---\n\nField json:\n===========\n{\"json\": {\"key\": \"value\"}}",
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'list',
				'subKey'             => '0',
				'expectedType'       => 'list',
				'expectedKeyData'    => 'one',
				'expectedRawKeyData' => 'one',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'list',
				'subKey'             => '1',
				'expectedType'       => 'list',
				'expectedKeyData'    => 'two',
				'expectedRawKeyData' => 'two',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'list',
				'subKey'             => '2',
				'expectedType'       => 'list',
				'expectedKeyData'    => "{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => '{"json": {"key": "value"}}',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'list',
				'subKey'             => null,
				'expectedType'       => 'list',
				'expectedKeyData'    => "Element 0:\n==========\none\n\n---\n\nElement 1:\n==========\ntwo\n\n---\n\nElement 2:\n==========\n{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => "Element 0:\n==========\none\n\n---\n\nElement 1:\n==========\ntwo\n\n---\n\nElement 2:\n==========\n{\"json\": {\"key\": \"value\"}}",
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'sorted set',
				'subKey'             => '0',
				'expectedType'       => 'zset',
				'expectedKeyData'    => 'one',
				'expectedRawKeyData' => 'one',
				'expectedHasScore'   => true,
				'expectedScore'      => 1,
			],
			[
				'key'                => 'sorted set',
				'subKey'             => '1',
				'expectedType'       => 'zset',
				'expectedKeyData'    => 'two',
				'expectedRawKeyData' => 'two',
				'expectedHasScore'   => true,
				'expectedScore'      => 2,
			],
			[
				'key'                => 'sorted set',
				'subKey'             => '2',
				'expectedType'       => 'zset',
				'expectedKeyData'    => 'two again',
				'expectedRawKeyData' => 'two again',
				'expectedHasScore'   => true,
				'expectedScore'      => 2,
			],
			[
				'key'                => 'sorted set',
				'subKey'             => '3',
				'expectedType'       => 'zset',
				'expectedKeyData'    => "{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => '{"json": {"key": "value"}}',
				'expectedHasScore'   => true,
				'expectedScore'      => 3,
			],
			[
				'key'                => 'sorted set',
				'subKey'             => null,
				'expectedType'       => 'zset',
				'expectedKeyData'    => "Member 0 (Score: 1):\n====================\none\n\n---\n\nMember 1 (Score: 2):\n====================\ntwo\n\n---\n\nMember 2 (Score: 2):\n====================\ntwo again\n\n---\n\nMember 3 (Score: 3):\n====================\n{\n    \"json\": {\n        \"key\": \"value\"\n    }\n}",
				'expectedRawKeyData' => "Member 0 (Score: 1):\n====================\none\n\n---\n\nMember 1 (Score: 2):\n====================\ntwo\n\n---\n\nMember 2 (Score: 2):\n====================\ntwo again\n\n---\n\nMember 3 (Score: 3):\n====================\n{\"json\": {\"key\": \"value\"}}",
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
			[
				'key'                => 'geo',
				'subKey'             => '0',
				'expectedType'       => 'zset',
				'expectedKeyData'    => 'Palermo',
				'expectedRawKeyData' => 'Palermo',
				'expectedHasScore'   => true,
				'expectedScore'      => 3479099956231200,
			],
			[
				'key'                => 'geo',
				'subKey'             => '1',
				'expectedType'       => 'zset',
				'expectedKeyData'    => 'Catania',
				'expectedRawKeyData' => 'Catania',
				'expectedHasScore'   => true,
				'expectedScore'      => 3479447370797100,
			],
			[
				'key'                => 'hyperLogLog',
				'subKey'             => null,
				'expectedType'       => 'string',
				'expectedKeyData'    => '(HyperLogLog encoded value)',
				'expectedRawKeyData' => 'HYLL',
				'expectedHasScore'   => false,
				'expectedScore'      => null,
			],
		];
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws NoServersConfigured
	 * @throws ServerConfigNotFound
	 */
	public function testResultFailsIfKeyIsUnknown() : void
	{
		$serverKey = '0';
		$key       = 'unknown-key';
		$subKey    = null;

		$query  = new FetchKeyInformationQuery( 0, $key, $subKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getServerManagerMock( $serverKey ) ))->handle( $query );

		self::assertFalse( $result->succeeded() );
		self::assertTrue( $result->failed() );
		self::assertSame( 'Key type not implemented or supported: unknown', $result->getMessage() );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws NoServersConfigured
	 * @throws ServerConfigNotFound
	 */
	public function testResultFailsIfConnectionToServerFailed() : void
	{
		$serverKey = '1';
		$key       = 'some-key';
		$subKey    = null;

		$query  = new FetchKeyInformationQuery( 0, $key, $subKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getServerManagerMock( $serverKey ) ))->handle( $query );

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
