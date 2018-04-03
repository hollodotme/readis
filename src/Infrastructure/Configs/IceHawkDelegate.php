<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Configs;

use IceHawk\IceHawk\Defaults\IceHawkDelegate as DefaultIceHawkDelegate;
use IceHawk\IceHawk\Interfaces\ProvidesRequestInfo;

final class IceHawkDelegate extends DefaultIceHawkDelegate
{
	public function setUpErrorHandling( ProvidesRequestInfo $requestInfo ) : void
	{
		error_reporting( E_ALL );
		ini_set( 'display_errors', 'On' );
	}
}
