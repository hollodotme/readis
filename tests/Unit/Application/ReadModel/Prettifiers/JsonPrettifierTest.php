<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\Prettifiers;

use hollodotme\Readis\Application\ReadModel\Prettifiers\JsonPrettifier;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;

final class JsonPrettifierTest extends TestCase
{
	/**
	 * @param string $input
	 * @param bool   $expectedCanUnserialize
	 * @param string $expectedOutput
	 *
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 *
	 * @dataProvider jsonInputProvider
	 */
	public function testCanPrettifyJsonString( string $input, bool $expectedCanUnserialize, string $expectedOutput ) : void
	{
		$jsonPrettifier = new JsonPrettifier();

		$this->assertSame( $expectedCanUnserialize, $jsonPrettifier->canPrettify( $input ) );
		$this->assertSame( $expectedOutput, $jsonPrettifier->prettify( $input ) );
	}

	public function jsonInputProvider() : array
	{
		return [
			[
				'input'                  => 'test-string',
				'expectedCanUnserialize' => false,
				'expectedOutput'         => 'test-string',
			],
			[
				'input'                  => '"test-string"',
				'expectedCanUnserialize' => false,
				'expectedOutput'         => '"test-string"',
			],
			[
				'input'                  => '[test-string]',
				'expectedCanUnserialize' => true,
				'expectedOutput'         => '[test-string]',
			],
			[
				'input'                  => '["test-string"]',
				'expectedCanUnserialize' => true,
				'expectedOutput'         => "[\n    \"test-string\"\n]",
			],
			[
				'input'                  => '{"test": {"key": "value"}}',
				'expectedCanUnserialize' => true,
				'expectedOutput'         => "{\n    \"test\": {\n        \"key\": \"value\"\n    }\n}",
			],
		];
	}
}
