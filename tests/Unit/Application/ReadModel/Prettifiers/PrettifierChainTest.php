<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\Prettifiers;

use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Prettifiers\PrettifierChain;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class PrettifierChainTest extends TestCase
{
	/**
	 * @param string $input
	 * @param string $expectedOutput
	 *
	 * @throws ExpectationFailedException
	 *
	 * @dataProvider inputProvider
	 */
	public function testCanPrettifyStrings( string $input, string $expectedOutput ) : void
	{
		$chain = new PrettifierChain();
		$chain->addPrettifiers(
			$this->getPrettifierMock( 'A' ),
			$this->getPrettifierMock( 'B' )
		);

		self::assertTrue( $chain->canPrettify( $input ) );
		self::assertSame( $expectedOutput, $chain->prettify( $input ) );
	}

	public function inputProvider() : array
	{
		return [
			[
				'input'          => 'SAM',
				'expectedOutput' => 'A: SAM',
			],
			[
				'input'          => 'BEN',
				'expectedOutput' => 'B: BEN',
			],
			[
				'input'          => 'BENJAMIN',
				'expectedOutput' => 'A: BENJAMIN',
			],
			[
				'input'          => 'CRIS',
				'expectedOutput' => 'CRIS',
			],
		];
	}

	private function getPrettifierMock( string $needle ) : PrettifiesString
	{
		return new class($needle) implements PrettifiesString
		{
			/** @var string */
			private $needle;

			public function __construct( string $needle )
			{
				$this->needle = $needle;
			}

			public function canPrettify( string $data ) : bool
			{
				return false !== strpos( $data, $this->needle );
			}

			public function prettify( string $data ) : string
			{
				return $this->needle . ': ' . $data;
			}
		};
	}
}
