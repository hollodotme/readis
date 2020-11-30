<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Application\Web\Responses\TwigPage;
use hollodotme\Readis\Exceptions\RuntimeException;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;

final class ServerSelectionRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handle( ProvidesReadRequestData $request ) : void
	{
		$env = $this->getEnv();

		$data = [
			'appConfig' => $env->getAppConfig(),
			'servers'   => $env->getServerConfigList()->getServerConfigs(),
		];

		(new TwigPage())->respond( 'Server/Read/Pages/ServerSelection.twig', $data );
	}
}
