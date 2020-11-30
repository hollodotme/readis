<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use Exception;
use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\ServerInformation;
use hollodotme\Readis\Application\ReadModel\Results\FetchServerInformationResult;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FetchServerInformationQueryHandler
{
	/** @var ProvidesRedisData */
	private $serverManager;

	public function __construct( ProvidesRedisData $serverManager )
	{
		$this->serverManager = $serverManager;
	}

	/**
	 * @return FetchServerInformationResult
	 * @throws Exception
	 */
	public function handle() : FetchServerInformationResult
	{
		try
		{
			$serverConfig    = $this->serverManager->getServerConfig();
			$slowLogCount    = $this->serverManager->getSlowLogCount();
			$slowLogsEntries = $this->serverManager->getSlowLogEntries();
			$serverInfo      = $this->serverManager->getServerInfo();

			$serverInformation = new ServerInformation(
				$serverConfig,
				$slowLogCount,
				$slowLogsEntries,
				$serverInfo
			);

			$result = new FetchServerInformationResult();
			$result->setServerInformation( $serverInformation );

			return $result;
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
