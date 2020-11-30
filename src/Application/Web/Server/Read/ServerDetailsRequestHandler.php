<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use Exception;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchServerInformationQueryHandler;
use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Application\Web\Responses\TwigPage;
use hollodotme\Readis\Exceptions\RuntimeException;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;

final class ServerDetailsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 * @throws Exception
	 */
	public function handle( ProvidesReadRequestData $request ) : void
	{
		$input     = $request->getInput();
		$appConfig = $this->getEnv()->getAppConfig();
		$database  = (string)$input->get( 'database', '0' );
		$serverKey = (string)$input->get( 'serverKey', '0' );

		$server        = $this->getEnv()->getServerConfigList()->getServerConfig( $serverKey );
		$serverManager = $this->getEnv()->getServerManagerForServerKey( $serverKey );

		$result = (new FetchServerInformationQueryHandler( $serverManager ))->handle();

		if ( $result->failed() )
		{
			$data = ['errorMessage' => $result->getMessage()];
			(new TwigPage())->respond( 'Theme/Error.twig', $data, 500 );

			return;
		}

		$serverInformation = $result->getServerInformation();
		$databases         = $this->getDatabases( $serverInformation->getServerConfig(), $server->getDatabaseMap() );

		$data = [
			'appConfig'           => $appConfig,
			'database'            => isset( $databases[ $database ] )
				? $database
				: (array_keys( $databases )[0] ?? null),
			'serverKey'           => $serverKey,
			'server'              => $server,
			'databases'           => $databases,
			'serverConfig'        => $serverInformation->getServerConfig(),
			'slowLogCount'        => $serverInformation->getSlowLogCount(),
			'slowLogEntries'      => $serverInformation->getSlowLogEntries(),
			'serverInfo'          => $serverInformation->getServerInfo(),
			'infoCommandDisabled' => !$serverManager->commandExists( 'INFO' ),
		];

		(new TwigPage())->respond( 'Server/Read/Pages/ServerDetails.twig', $data );
	}

	private function getDatabases( array $serverConfig, array $databaseMap ) : array
	{
		$databases = [];

		if ( isset( $serverConfig['databases'] ) )
		{
			for ( $i = 0; $i < (int)$serverConfig['databases']; $i++ )
			{
				$databases[ (string)$i ] = $databaseMap[ (string)$i ] ?? "Database {$i}";
			}

			return $databases;
		}

		return $databaseMap;
	}
}
