<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\ReadModel\Prettifiers;

use hollodotme\Readis\Application\ReadModel\Prettifiers\HyperLogLogPrettifier;
use PHPUnit\Framework\TestCase;

final class HyperLogLogPrettifierTest extends TestCase
{
	/**
	 * @param string $value
	 * @param bool   $expectedResult
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider canPrettifyProvider
	 */
	public function testCanPrettify( string $value, bool $expectedResult ) : void
	{
		$prettifier = new HyperLogLogPrettifier();

		$this->assertSame( $expectedResult, $prettifier->canPrettify( $value ) );
	}

	public function canPrettifyProvider() : array
	{
		return [
			[
				'value'          => 'Some random value',
				'expectedResult' => false,
			],
			[
				'value'          => 'HYLL Some random value',
				'expectedResult' => true,
			],
		];
	}

	/**
	 * @param string $value
	 * @param string $expectedPrettyValue
	 *
	 * @throws \PHPUnit\Framework\ExpectationFailedException
	 * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
	 *
	 * @dataProvider prettifyDataProvider
	 */
	public function testCanGetPrettifiedValue( string $value, string $expectedPrettyValue ) : void
	{
		$prettifier = new HyperLogLogPrettifier();

		$this->assertSame( $expectedPrettyValue, $prettifier->prettify( $value ) );
	}

	public function prettifyDataProvider() : array
	{
		return [
			[
				'value'               => 'Some random value',
				'expectedPrettyValue' => "Some random value\n\n(HyperLogLog encoded value)",
			],
			[
				'value'               => 'HYLL Some random value',
				'expectedPrettyValue' => "HYLL Some random value\n\n(HyperLogLog encoded value)",
			],
		];
	}
}
