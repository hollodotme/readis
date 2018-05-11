<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInformation;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\HashKeyNames;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\DTO\KeyName;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesHashKeyNames;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\Prettifiers\JsonPrettifier;
use hollodotme\Readis\Application\ReadModel\Prettifiers\PrettifierChain;
use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\Results\FetchKeyInformationResult;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use function implode;
use function str_repeat;

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

			$keyInfo = $manager->getKeyInfoObject( $query->getKeyName() );

			$keyName = new KeyName( $query->getKeyName() );
			if ( null !== $query->getHashKey() )
			{
				$keyName = new HashKeyNames( $query->getKeyName(), $query->getHashKey() );
			}

			$keyData = $this->getKeyData( $manager, $keyInfo, $keyName );

			$result = new FetchKeyInformationResult();
			$result->setRawKeyData( $keyData->getRawKeyData() );
			$result->setKeyData( $keyData->getKeyData() );
			$result->setKeyInfo( $keyInfo );

			return $result;
		}
		catch ( ServerConfigNotFound $e )
		{
			return new FetchKeyInformationResult( ResultType::FAILURE, $e->getMessage() );
		}
		catch ( ConnectionFailedException $e )
		{
			return new FetchKeyInformationResult(
				ResultType::FAILURE,
				sprintf( 'Could not connect to redis server: %s', $e->getMessage() )
			);
		}
	}

	/**
	 * @param ServerManager          $manager
	 * @param ProvidesKeyInformation $keyInfo
	 * @param ProvidesKeyName        $keyName
	 *
	 * @throws ConnectionFailedException
	 * @return ProvidesKeyData
	 */
	private function getKeyData(
		ServerManager $manager,
		ProvidesKeyInformation $keyInfo,
		ProvidesKeyName $keyName
	) :
	ProvidesKeyData
	{
		$prettifier = new PrettifierChain();
		$prettifier->addPrettifiers( new JsonPrettifier() );

		if ( KeyType::HASH === $keyInfo->getType() && $keyName instanceof ProvidesHashKeyNames )
		{
			$rawKeyData = $manager->getHashValue( $keyName->getKeyName(), $keyName->getHashKeyName() ) ?: '';
			$keyData    = $prettifier->prettify( $rawKeyData );

			return new KeyData( $keyData, $rawKeyData );
		}

		if ( KeyType::LIST === $keyInfo->getType() && $keyName instanceof ProvidesHashKeyNames )
		{
			$rawKeyData = $manager->getListValue( $keyName->getKeyName(), (int)$keyName->getHashKeyName() ) ?: '';
			$keyData    = $prettifier->prettify( $rawKeyData );

			return new KeyData( $keyData, $rawKeyData );
		}

		if ( KeyType::LIST === $keyInfo->getType() )
		{
			$rawListItems = [];
			foreach ( $keyInfo->getSubItems() as $index => $listItem )
			{
				$rawListItems[] = sprintf(
					"Element %d:\n%s\n%s",
					$index,
					str_repeat( '=', 8 + strlen( (string)$index ) ),
					$listItem
				);
			}

			$prettyListItems = [];
			foreach ( $keyInfo->getSubItems() as $index => $listItem )
			{
				$prettyListItem    = $prettifier->prettify( $listItem );
				$prettyListItems[] = sprintf(
					"Element %d:\n%s\n%s",
					$index,
					str_repeat( '=', 8 + strlen( (string)$index ) ),
					$prettyListItem
				);
			}

			$rawKeyData = implode( "\n\n---\n\n", $rawListItems );
			$keyData    = implode( "\n\n---\n\n", $prettyListItems );

			return new KeyData( $keyData, $rawKeyData );
		}

		$rawKeyData = $manager->getValue( $keyName->getKeyName() ) ?: '';
		$keyData    = $prettifier->prettify( $rawKeyData );

		return new KeyData( $keyData, $rawKeyData );
	}
}
