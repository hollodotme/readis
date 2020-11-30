<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\HashKeyDataBuilder;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

final class HashKeyDataBuilderTest extends AbstractKeyDataBuilderTest
{
	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function testBuildKeyData() : void
	{
		$keyDataBuilder = new HashKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'hash' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'getKeyName' )->willReturn( 'hash' );
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );
		$keyNameStub->method( 'getSubKey' )->willReturn( null );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		$keyData = $keyDataBuilder->buildKeyData( $keyInfoStub, $keyNameStub );

		$expectedKeyData    = "Field field:\n============\nPretty: value"
		                      . "\n\n---\n\n"
		                      . "Field json:\n===========\nPretty: {\"json\": {\"key\": \"value\"}}";
		$expectedRawKeyData = "Field field:\n============\nvalue"
		                      . "\n\n---\n\n"
		                      . "Field json:\n===========\n{\"json\": {\"key\": \"value\"}}";

		self::assertSame( $expectedKeyData, $keyData->getKeyData() );
		self::assertSame( $expectedRawKeyData, $keyData->getRawKeyData() );
		self::assertFalse( $keyData->hasScore() );
		self::assertNull( $keyData->getScore() );
	}

	/**
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 */
	public function testCanBuildKeyData() : void
	{
		$keyDataBuilder = new HashKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'hash' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		self::assertTrue( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );
	}

	/**
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 */
	public function testCanNotBuildKeyData() : void
	{
		$keyDataBuilder = new HashKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'hash' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'hasSubKey' )->willReturn( true );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		self::assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );

		$keyInfoStub->method( 'getType' )->willReturn( 'string' );
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );

		self::assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );
	}
}
