<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\Queries\FindKeysInDatabaseQuery;
use hollodotme\Readis\Application\ReadModel\Results\FindKeysInDatabaseResult;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FindKeysInDatabaseQueryHandler
{
	/** @var ProvidesRedisData */
	private $serverManager;

	public function __construct( ProvidesRedisData $serverManager )
	{
		$this->serverManager = $serverManager;
	}

	public function handle( FindKeysInDatabaseQuery $query ) : FindKeysInDatabaseResult
	{
		try
		{
			$this->serverManager->selectDatabase( $query->getDatabase() );
			$keyInfoObjects = $this->serverManager->getKeyInfoObjects( $query->getSearchPattern(), $query->getLimit() );

			$result = new FindKeysInDatabaseResult();
			$result->setKeyInfoObjects( ...$keyInfoObjects );

			return $result;
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
