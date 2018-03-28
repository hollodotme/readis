<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;

final class ServerDetailsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	public function handle( ProvidesReadRequestData $request )
	{
		// TODO: Implement handle() method.
	}
}
