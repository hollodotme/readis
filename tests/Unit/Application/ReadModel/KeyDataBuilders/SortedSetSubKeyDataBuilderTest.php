<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SortedSetSubKeyDataBuilder;
use hollodotme\Readis\Exceptions\RuntimeException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;

final class SortedSetSubKeyDataBuilderTest extends AbstractKeyDataBuilderTest
{
	/**
	 * @throws ExpectationFailedException
	 * @throws RuntimeException
	 * @throws Exception
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testBuildKeyData() : void
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'getKeyName' )->willReturn( 'sorted set' );
		$keyNameStub->method( 'getSubKey' )->willReturn( '1' );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		$keyData = $keyDataBuilder->buildKeyData( $keyInfoStub, $keyNameStub );

		$expectedKeyData    = 'Pretty: {"json": {"key": "value"}}';
		$expectedRawKeyData = '{"json": {"key": "value"}}';

		self::assertSame( $expectedKeyData, $keyData->getKeyData() );
		self::assertSame( $expectedRawKeyData, $keyData->getRawKeyData() );
		self::assertTrue( $keyData->hasScore() );
		self::assertSame( 2.0, $keyData->getScore() );
	}

	/**
	 * @throws Exception
	 * @throws RuntimeException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testBuildKeyDataThrowsExceptionForNotExistingSubKey() : void
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'getKeyName' )->willReturn( 'sorted set' );
		$keyNameStub->method( 'getSubKey' )->willReturn( '2' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key in sorted set anymore.' );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		/** @noinspection UnusedFunctionResultInspection */
		$keyDataBuilder->buildKeyData( $keyInfoStub, $keyNameStub );
	}

	/**
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testCanBuildKeyData() : void
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'hasSubKey' )->willReturn( true );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		self::assertTrue( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );
	}

	/**
	 * @throws Exception
	 * @throws ExpectationFailedException
	 * @throws \PHPUnit\Framework\MockObject\RuntimeException
	 */
	public function testCanNotBuildKeyData() : void
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keyNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keyNameStub->method( 'hasSubKey' )->willReturn( false );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keyNameStub */

		self::assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );

		$keyInfoStub->method( 'getType' )->willReturn( 'string' );
		$keyNameStub->method( 'hasSubKey' )->willReturn( true );

		self::assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keyNameStub ) );
	}
}
