<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Exceptions\RuntimeException;
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

		$this->manager->method( 'getSetMember' )
		              ->will(
			              $this->returnCallback(
				              function ( string $key, int $index )
				              {
					              if ( 'set' === $key && 0 === $index )
					              {
						              return '{"json": {"key": "value"}}';
					              }

					              throw new RuntimeException( 'Could not find member in set anymore.' );
				              }
			              )
		              );

		$this->manager->method( 'getListElement' )
		              ->will(
			              $this->returnCallback(
				              function ( string $key, int $index )
				              {
					              if ( 'list' === $key && 0 === $index )
					              {
						              return '{"json": {"key": "value"}}';
					              }

					              throw new RuntimeException( 'Could not find element in list anymore.' );
				              }
			              )
		              );

		$this->manager->method( 'getHashValue' )
		              ->will(
			              $this->returnCallback(
				              function ( string $key, string $hashKey )
				              {
					              if ( 'hash' === $key && 'json' === $hashKey )
					              {
						              return '{"json": {"key": "value"}}';
					              }

					              throw new RuntimeException( 'Could not find field in hash anymore.' );
				              }
			              )
		              );

		$this->manager->method( 'getAllHashValues' )->willReturn(
			[
				'field' => 'value',
				'json'  => '{"json": {"key": "value"}}',
			]
		);

		$this->manager->method( 'getAllListElements' )->willReturn(
			[
				'value',
				'{"json": {"key": "value"}}',
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