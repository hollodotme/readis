<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Infrastructure\Configs;

use hollodotme\Readis\Infrastructure\Configs\IceHawkDelegate;
use IceHawk\IceHawk\Interfaces\ProvidesRequestInfo;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use function error_reporting;
use function ini_get;
use function ini_set;
use const E_ALL;
use const E_ERROR;

final class IceHawkDelegateTest extends TestCase
{
	protected function setUp() : void
	{
		error_reporting( E_ERROR );
		ini_set( 'display_errors', 'Off' );
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws Exception
	 * @throws RuntimeException
	 */
	public function testCanSetUpErrorHandling() : void
	{
		/** @var ProvidesRequestInfo $requestInfo */
		$requestInfo = $this->getMockBuilder( ProvidesRequestInfo::class )->getMockForAbstractClass();

		$delegate = new IceHawkDelegate();
		$delegate->setUpErrorHandling( $requestInfo );

		self::assertSame( E_ALL, error_reporting() );
		self::assertSame( 'On', ini_get( 'display_errors' ) );
	}
}
