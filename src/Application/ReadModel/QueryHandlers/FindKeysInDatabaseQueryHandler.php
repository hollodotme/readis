<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\Queries\FindKeysInDatabaseQuery;
use hollodotme\Readis\Application\ReadModel\Results\FindKeysInDatabaseResult;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FindKeysInDatabaseQueryHandler extends AbstractQueryHandler
{
	public function handle( FindKeysInDatabaseQuery $query ) : FindKeysInDatabaseResult
	{
		try
		{
			$serverConfigList = $this->getEnv()->getServerConfigList();
			$serverConfig     = $serverConfigList->getServerConfig( $query->getServerKey() );
			$manager          = $this->getEnv()->getServerManager( $serverConfig );

			$manager->selectDatabase( $query->getDatabase() );
			$keyInfoObjects = $manager->getKeyInfoObjects( $query->getSearchPattern(), $query->getLimit() );

			$result = new FindKeysInDatabaseResult();
			$result->setKeyInfoObjects( ...$keyInfoObjects );

			return $result;
		}
		catch ( ServerConfigNotFound $e )
		{
			return new FindKeysInDatabaseResult(
				ResultType::FAILURE,
				sprintf( 'Could not find configuration for server key: %s', $e->getServerKey() )
			);
		}
		catch ( ConnectionFailedException $e )
		{
			return new FindKeysInDatabaseResult(
				ResultType::FAILURE,
				sprintf( 'Could not connect to redis server: %s', $e->getMessage() )
			);
		}
	}
}
