<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Integration;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Env;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use function copy;
use function file_exists;
use function rename;
use function unlink;

final class EnvTest extends TestCase
{
	/** @var null|string */
	private $oldServersConfigFile;

	protected function setUp() : void
	{
		$serversConfigFile       = __DIR__ . '/../../config/servers.php';
		$oldServersConfigFile    = __DIR__ . '/../../config/servers.old.php';
		$sampleServersConfigFile = __DIR__ . '/../../config/servers.sample.php';

		if ( file_exists( $serversConfigFile ) )
		{
			@rename( $serversConfigFile, $oldServersConfigFile );
			$this->oldServersConfigFile = $oldServersConfigFile;
		}

		@copy( $sampleServersConfigFile, $serversConfigFile );
	}

	protected function tearDown() : void
	{
		$serversConfigFile = __DIR__ . '/../../config/servers.php';

		@unlink( $serversConfigFile );

		if ( null !== $this->oldServersConfigFile )
		{
			@rename( $this->oldServersConfigFile, $serversConfigFile );
			$this->oldServersConfigFile = null;
		}
	}

	/**
	 * @throws ExpectationFailedException
	 * @throws InvalidArgumentException
	 * @throws ServerConfigNotFound
	 */
	public function testCanGetServerManagerForServerKey() : void
	{
		$env = new Env();

		$serverManager = $env->getServerManagerForServerKey( '0' );

		$this->assertInstanceOf( ProvidesRedisData::class, $serverManager );
	}
}
