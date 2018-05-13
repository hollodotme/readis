<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use PHPUnit\Framework\TestCase;

abstract class AbstractKeyDataBuilderTest extends TestCase
{
	/** @var ProvidesRedisData */
	private $manager;

	/** @var PrettifiesString */
	private $prettifier;

	protected function setUp() : void
	{
		$this->manager = $this->getMockBuilder( ProvidesRedisData::class )
		                      ->getMockForAbstractClass();
		$this->manager->method( 'getValue' )->with( 'string' )->willReturn( '{"json": {"key": "value"}}' );
		$this->manager->method( 'getAllSortedSetMembers' )
		              ->with( 'sorted set' )
		              ->willReturn(
			              [
				              'one'                        => 1.0,
				              '{"json": {"key": "value"}}' => 2.0,
			              ]
		              );

		$this->prettifier = new class implements PrettifiesString
		{
			public function canPrettify( string $data ) : bool
			{
				return true;
			}

			public function prettify( string $data ) : string
			{
				return 'Pretty: ' . $data;
			}
		};
	}

	protected function tearDown() : void
	{
		$this->manager = null;
	}

	final protected function getManager() : ProvidesRedisData
	{
		return $this->manager;
	}

	final protected function getPrettifier() : PrettifiesString
	{
		return $this->prettifier;
	}
}