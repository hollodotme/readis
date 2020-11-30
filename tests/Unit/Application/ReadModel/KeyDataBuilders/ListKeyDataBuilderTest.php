<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\ListKeyDataBuilder;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;

final class ListKeyDataBuilderTest extends AbstractKeyDataBuilderTest
{
	/**
	 * @throws ConnectionFailedException
	 * @throws ExpectationFailedException
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function testBuildKeyData() : void
	{
		$keyDataBuilder = new ListKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'list' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'getKeyName' )->willReturn( 'list' );
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );
		$keyNameStub->method( 'getSubKey' )->willReturn( null );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		$keyData = $keyDataBuilder->buildKeyData( $keyInfoStub, $keyNameStub );

		$expectedKeyData    = "Element 0:\n==========\nPretty: value"
		                      . "\n\n---\n\n"
		                      . "Element 1:\n==========\nPretty: {\"json\": {\"key\": \"value\"}}";
		$expectedRawKeyData = "Element 0:\n==========\nvalue"
		                      . "\n\n---\n\n"
		                      . "Element 1:\n==========\n{\"json\": {\"key\": \"value\"}}";

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
		$keyDataBuilder = new ListKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'list' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );
		$keyNameStub->method( 'getSubKey' )->willReturn( null );

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
		$keyDataBuilder = new ListKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'list' );
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
