<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Configs;

use IceHawk\IceHawk\Interfaces\ProvidesRequestInfo;
use IceHawk\IceHawk\Interfaces\SetsUpEnvironment;

final class IceHawkDelegate implements SetsUpEnvironment
{
	public function setUpGlobalVars() : void
	{
	}

	public function setUpErrorHandling( ProvidesRequestInfo $requestInfo ) : void
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 1 );
	}

	public function setUpSessionHandling( ProvidesRequestInfo $requestInfo ) : void
	{
	}
}
