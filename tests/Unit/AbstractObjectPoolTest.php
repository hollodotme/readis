<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit;

use hollodotme\Readis\AbstractObjectPool;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class AbstractObjectPoolTest extends TestCase
{
	/**
	 * @throws ExpectationFailedException
	 */
	public function testCanGetSharedInstance() : void
	{
		$pool = $this->getObjectPool();

		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$instanceA = $pool->getObjectInstance();

		/** @noinspection PhpPossiblePolymorphicInvocationInspection */
		$instanceB = $pool->getObjectInstance();

		self::assertSame( $instanceA, $instanceB );
	}

	private function getObjectPool() : AbstractObjectPool
	{
		return new class extends AbstractObjectPool {
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
