<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\ServerInformation;
use hollodotme\Readis\Application\ReadModel\Queries\FetchServerInformationQuery;
use hollodotme\Readis\Application\ReadModel\Results\FetchServerInformationResult;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FetchServerInformationQueryHandler extends AbstractQueryHandler
{
	/**
	 * @param FetchServerInformationQuery $query
	 *
	 * @return FetchServerInformationResult
	 * @throws \Exception
	 */
	public function handle( FetchServerInformationQuery $query ) : FetchServerInformationResult
	{
		try
		{
			$serverConfigList = $this->getEnv()->getServerConfigList();
			$server           = $serverConfigList->getServerConfig( $query->getServerKey() );
			$serverManager    = $this->getEnv()->getServerManager( $server );

			$serverConfig    = $serverManager->getServerConfig();
			$slowLogCount    = $serverManager->getSlowLogCount();
			$slowLogsEntries = $serverManager->getSlowLogEntries();
			$serverInfo      = $serverManager->getServerInfo();

			$serverInformation = new ServerInformation(
				$server,
				$serverConfig,
				$slowLogCount,
				$slowLogsEntries,
				$serverInfo
			);

			$result = new FetchServerInformationResult();
			$result->setServerInformation( $serverInformation );

			return $result;
		}
		catch ( ServerConfigNotFound $e )
		{
			return new FetchServerInformationResult( ResultType::FAILURE, $e->getMessage() );
		}
		catch ( ConnectionFailedException $e )
		{
			return new FetchServerInformationResult(
				ResultType::FAILURE,
				sprintf( 'Could not connect to redis server: %s', $e->getMessage() )
			);
		}
	}
}
