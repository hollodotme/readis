<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SortedSetSubKeyDataBuilder;
use hollodotme\Readis\Exceptions\RuntimeException;

final class SortedSetSubKeyDataBuilderTest extends AbstractKeyDataBuilderTest
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws \hollodotme\Readis\Exceptions\RuntimeException
	 */
	public function testBuildKeyData()
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keysNameStub->method( 'getKeyName' )->willReturn( 'sorted set' );
		$keysNameStub->method( 'getSubKey' )->willReturn( '1' );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keysNameStub */

		$keyData = $keyDataBuilder->buildKeyData( $keyInfoStub, $keysNameStub );

		$expectedKeyData    = 'Pretty: {"json": {"key": "value"}}';
		$expectedRawKeyData = '{"json": {"key": "value"}}';

		$this->assertSame( $expectedKeyData, $keyData->getKeyData() );
		$this->assertSame( $expectedRawKeyData, $keyData->getRawKeyData() );
		$this->assertTrue( $keyData->hasScore() );
		$this->assertSame( 2.0, $keyData->getScore() );
	}

	/**
	 * @throws RuntimeException
	 */
	public function testBuildKeyDataThrowsExceptionForNotExistingSubKey() : void
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keysNameStub->method( 'getKeyName' )->willReturn( 'sorted set' );
		$keysNameStub->method( 'getSubKey' )->willReturn( '2' );

		$this->expectException( RuntimeException::class );
		$this->expectExceptionMessage( 'Could not find key in sorted set anymore.' );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keysNameStub */

		$keyDataBuilder->buildKeyData( $keyInfoStub, $keysNameStub );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanBuildKeyData()
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keysNameStub->method( 'hasSubKey' )->willReturn( true );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keysNameStub */

		$this->assertTrue( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keysNameStub ) );
	}

	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 */
	public function testCanNotBuildKeyData()
	{
		$keyDataBuilder = new SortedSetSubKeyDataBuilder( $this->getManager(), $this->getPrettifier() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'zset' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();
		$keysNameStub->method( 'hasSubKey' )->willReturn( false );

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keysNameStub */

		$this->assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keysNameStub ) );

		$keyInfoStub->method( 'getType' )->willReturn( 'string' );
		$keysNameStub->method( 'hasSubKey' )->willReturn( true );

		$this->assertFalse( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keysNameStub ) );
	}
}
