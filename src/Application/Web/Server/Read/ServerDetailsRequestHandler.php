<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\TwigPage;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;

final class ServerDetailsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handle( ProvidesReadRequestData $request )
	{
		$input            = $request->getInput();
		$appConfig        = $this->getEnv()->getAppConfig();
		$serverConfigList = $this->getEnv()->getServerConfigList();
		$serverKey        = (string)$input->get( 'serverKey', '0' );
		$serverConfig     = $serverConfigList->getServerConfig( $serverKey );
		$serverManager    = $this->getEnv()->getServerManager( $serverConfig );
		$database         = (string)$input->get( 'database', '0' );

		$data = [
			'appConfig'     => $appConfig,
			'server'        => $serverConfig,
			'database'      => $database,
			'serverKey'     => $serverKey,
			'databaseMap'   => $serverConfig->getDatabaseMap(),
			'serverConfig'  => $serverManager->getServerConfig(),
			'slowLogLength' => $serverManager->getSlowLogLength(),
			'slowLogs'      => $serverManager->getSlowLogs(),
			'serverInfo'    => $serverManager->getServerInfo(),
			'manager'       => $serverManager,
		];

		(new TwigPage())->respond( 'Server/Read/Pages/ServerDetails.twig', $data );
	}
}
