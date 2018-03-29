<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit;

use hollodotme\Readis\AbstractObjectPool;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use stdClass;

final class AbstractObjectPoolTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 */
	public function testCanGetSharedInstance() : void
	{
		$pool = $this->getObjectPool();

		$instanceA = $pool->getObjectInstance();
		$instanceB = $pool->getObjectInstance();

		$this->assertSame( $instanceA, $instanceB );
	}

	private function getObjectPool()
	{
		return new class extends AbstractObjectPool
		{
			public function getObjectInstance() : stdClass
			{
				return $this->getSharedInstance(
					'object',
					function ()
					{
						return new stdClass();
					}
				);
			}
		};
	}
}
