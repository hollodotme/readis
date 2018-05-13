<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\KeyDataBuilder;
use hollodotme\Readis\Exceptions\KeyTypeNotImplemented;
use PHPUnit\Framework\TestCase;

final class KeyDataBuilderTest extends TestCase
{
	/**
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 * @throws KeyTypeNotImplemented
	 */
	public function testBuildKeyData()
	{
		$keyDataBuilder = new KeyDataBuilder( $this->getBuilderMock() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'hash' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();

		/** @var ProvidesKeyInfo $keyInfoStub */
		/** @var ProvidesKeyName $keysNameStub */
		$keyData = $keyDataBuilder->buildKeyData( $keyInfoStub, $keysNameStub );

		$this->assertSame( 'keyData', $keyData->getKeyData() );
		$this->assertSame( 'rawKeyData', $keyData->getRawKeyData() );
		$this->assertFalse( $keyData->hasScore() );
		$this->assertNull( $keyData->getScore() );
	}

	private function getBuilderMock() : BuildsKeyData
	{
		return new class implements BuildsKeyData
		{
			public function canBuildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : bool
			{
				return ($keyInfo->getType() !== 'unknown');
			}

			public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
			{
				return new class implements ProvidesKeyData
				{
					public function getKeyData() : string
					{
						return 'keyData';
					}

					public function getRawKeyData() : string
					{
						return 'rawKeyData';
					}

					public function hasScore() : bool
					{
						return false;
					}

					public function getScore() : ?float
					{
						return null;
					}
				};
			}
		};
	}

	/**
	 * @throws KeyTypeNotImplemented
	 */
	public function testBuildKeyDataThrowsExceptionForUnknownKeyType()
	{
		$keyDataBuilder = new KeyDataBuilder( $this->getBuilderMock() );

		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		$keyInfoStub->method( 'getType' )->willReturn( 'unknown' );
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();

		$this->expectException( KeyTypeNotImplemented::class );
		$this->expectExceptionMessage( 'Key type not implemented or supported: unknown' );

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
		$keyDataBuilder = new KeyDataBuilder( $this->getBuilderMock() );

		/** @var ProvidesKeyInfo $keyInfoStub */
		$keyInfoStub = $this->getMockBuilder( ProvidesKeyInfo::class )->getMockForAbstractClass();
		/** @var ProvidesKeyName $keysNameStub */
		$keysNameStub = $this->getMockBuilder( ProvidesKeyName::class )->getMockForAbstractClass();

		$this->assertTrue( $keyDataBuilder->canBuildKeyData( $keyInfoStub, $keysNameStub ) );
	}
}
