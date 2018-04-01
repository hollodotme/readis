<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\Results\FetchKeyInformationResult;
use hollodotme\Readis\Application\StringUnserializers\JsonPrettyfier;
use hollodotme\Readis\Application\StringUnserializers\UnserializerChain;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FetchKeyInformationQueryHandler extends AbstractQueryHandler
{
	public function handle( FetchKeyInformationQuery $query ) : FetchKeyInformationResult
	{
		try
		{
			$serverConfigList = $this->getEnv()->getServerConfigList();
			$serverConfig     = $serverConfigList->getServerConfig( $query->getServerKey() );
			$manager          = $this->getEnv()->getServerManager( $serverConfig );

			$manager->selectDatabase( $query->getDatabase() );

			$unserializer = new UnserializerChain();
			$unserializer->addUnserializers( new JsonPrettyfier() );

			if ( null === $query->getHashKey() )
			{
				$keyData = $manager->getValueAsUnserializedString( $query->getKeyName(), $unserializer );
			}
			else
			{
				$keyData = $manager->getHashValueAsUnserializedString(
					$query->getKeyName(),
					$query->getHashKey(),
					$unserializer
				);
			}

			$keyInfo = $manager->getKeyInfoObject( $query->getKeyName() );

			$result = new FetchKeyInformationResult();
			$result->setKeyData( $keyData );
			$result->setKeyInfo( $keyInfo );

			return $result;
		}
		catch ( ServerConfigNotFound $e )
		{
			return new FetchKeyInformationResult(
				ResultType::FAILURE,
				sprintf( 'Could not find configuration for server key: %s', $e->getServerKey() )
			);
		}
		catch ( ConnectionFailedException $e )
		{
			return new FetchKeyInformationResult(
				ResultType::FAILURE,
				sprintf( 'Could not connect to redis server: %s', $e->getMessage() )
			);
		}
	}
}
