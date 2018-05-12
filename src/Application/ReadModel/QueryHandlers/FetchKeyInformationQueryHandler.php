<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\DTO\KeyName;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\Prettifiers\HyperLogLogPrettifier;
use hollodotme\Readis\Application\ReadModel\Prettifiers\JsonPrettifier;
use hollodotme\Readis\Application\ReadModel\Prettifiers\PrettifierChain;
use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\Results\FetchKeyInformationResult;
use hollodotme\Readis\Exceptions\KeyTypeNotImplemented;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;
use hollodotme\Readis\Interfaces\ProvidesInfrastructure;
use function implode;
use function str_repeat;

final class FetchKeyInformationQueryHandler extends AbstractQueryHandler
{
	/** @var PrettifiesString */
	private $prettifier;

	public function __construct( ProvidesInfrastructure $env )
	{
		parent::__construct( $env );

		$this->prettifier = new PrettifierChain();
		$this->prettifier->addPrettifiers(
			new JsonPrettifier(),
			new HyperLogLogPrettifier()
		);
	}

	/**
	 * @param FetchKeyInformationQuery $query
	 *
	 * @throws KeyTypeNotImplemented
	 * @return FetchKeyInformationResult
	 */
	public function handle( FetchKeyInformationQuery $query ) : FetchKeyInformationResult
	{
		try
		{
			$serverConfigList = $this->getEnv()->getServerConfigList();
			$serverConfig     = $serverConfigList->getServerConfig( $query->getServerKey() );
			$manager          = $this->getEnv()->getServerManager( $serverConfig );

			$manager->selectDatabase( $query->getDatabase() );

			$keyInfo = $manager->getKeyInfoObject( $query->getKeyName() );

			$keyName = new KeyName( $query->getKeyName(), $query->getHashKey() );
			$keyData = $this->getKeyData( $manager, $keyInfo, $keyName );

			$result = new FetchKeyInformationResult();
			$result->setKeyData( $keyData );
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
	 * @param ServerManager   $manager
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @throws KeyTypeNotImplemented
	 * @return ProvidesKeyData
	 */
	private function getKeyData(
		ServerManager $manager,
		ProvidesKeyInfo $keyInfo,
		ProvidesKeyName $keyName
	) : ProvidesKeyData
	{
		if ( $keyName->hasSubKey() )
		{
			return $this->getSubKeyData( $manager, $keyInfo, $keyName );
		}

		return $this->getWholeKeyData( $manager, $keyInfo, $keyName );
	}

	/**
	 * @param ServerManager   $manager
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @throws KeyTypeNotImplemented
	 * @return ProvidesKeyData
	 */
	private function getSubKeyData(
		ServerManager $manager,
		ProvidesKeyInfo $keyInfo,
		ProvidesKeyName $keyName
	) : ProvidesKeyData
	{
		if ( KeyType::HASH === $keyInfo->getType() )
		{
			$rawKeyData = $manager->getHashValue( $keyName->getKeyName(), $keyName->getSubKey() ) ?: '';
			$keyData    = $this->prettifier->prettify( $rawKeyData );

			return new KeyData( $keyData, $rawKeyData );
		}

		if ( KeyType::LIST === $keyInfo->getType() )
		{
			$rawKeyData = $manager->getListValue( $keyName->getKeyName(), (int)$keyName->getSubKey() ) ?: '';
			$keyData    = $this->prettifier->prettify( $rawKeyData );

			return new KeyData( $keyData, $rawKeyData );
		}

		if ( KeyType::SET === $keyInfo->getType() )
		{
			$rawKeyData = $keyInfo->getSubItems()[ (int)$keyName->getSubKey() ];
			$keyData    = $this->prettifier->prettify( $rawKeyData );

			return new KeyData( $keyData, $rawKeyData );
		}

		if ( KeyType::SORTED_SET === $keyInfo->getType() )
		{
			$i = 0;
			foreach ( $keyInfo->getSubItems() as $member => $score )
			{
				if ( (int)$keyName->getSubKey() !== $i++ )
				{
					continue;
				}

				$rawKeyData = $member;
				$keyData    = $this->prettifier->prettify( $member );

				return new KeyData( $keyData, $rawKeyData, $score );
			}
		}

		throw new KeyTypeNotImplemented(
			'Key type not implemented or does not support sub keys: ' . $keyInfo->getType()
		);
	}

	/**
	 * @param ServerManager   $manager
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @throws KeyTypeNotImplemented
	 * @return ProvidesKeyData
	 */
	private function getWholeKeyData(
		ServerManager $manager,
		ProvidesKeyInfo $keyInfo,
		ProvidesKeyName $keyName
	) : ProvidesKeyData
	{
		if ( KeyType::HASH === $keyInfo->getType() )
		{
			return $this->getKeyDataForWholeHash( $manager, $keyName );
		}

		if ( KeyType::LIST === $keyInfo->getType() )
		{
			return $this->getKeyDataForWholeList( $keyInfo );
		}

		if ( KeyType::SET === $keyInfo->getType() )
		{
			return $this->getKeyDataForWholeSet( $keyInfo );
		}

		if ( KeyType::SORTED_SET === $keyInfo->getType() )
		{
			return $this->getKeyDataForWholeSortedSet( $keyInfo );
		}

		if ( KeyType::STRING === $keyInfo->getType() )
		{
			return $this->getKeyDataForString( $manager, $keyName );
		}

		throw new KeyTypeNotImplemented( 'Key type not implemented: ' . $keyInfo->getType() );
	}

	/**
	 * @param ServerManager   $manager
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @return ProvidesKeyData
	 */
	private function getKeyDataForWholeHash( ServerManager $manager, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$rawListItems = [];
		$hashValues   = $manager->getAllHashValues( $keyName->getKeyName() );

		foreach ( $hashValues as $hashKey => $hashValue )
		{
			$rawListItems[] = sprintf(
				"Hash key %s:\n%s\n%s",
				$hashKey,
				str_repeat( '=', 10 + strlen( (string)$hashKey ) ),
				$hashValue
			);
		}

		$prettyListItems = [];
		foreach ( $hashValues as $hashKey => $hashValue )
		{
			$prettyListItem    = $this->prettifier->prettify( $hashValue );
			$prettyListItems[] = sprintf(
				"Hash key %s:\n%s\n%s",
				$hashKey,
				str_repeat( '=', 10 + strlen( (string)$hashKey ) ),
				$prettyListItem
			);
		}

		$rawKeyData = implode( "\n\n---\n\n", $rawListItems );
		$keyData    = implode( "\n\n---\n\n", $prettyListItems );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getKeyDataForWholeList( ProvidesKeyInfo $keyInfo ) : ProvidesKeyData
	{
		$rawListItems = [];
		foreach ( $keyInfo->getSubItems() as $index => $listItem )
		{
			$rawListItems[] = sprintf(
				"Element %d:\n%s\n%s",
				$index,
				str_repeat( '=', 9 + strlen( (string)$index ) ),
				$listItem
			);
		}

		$prettyListItems = [];
		foreach ( $keyInfo->getSubItems() as $index => $listItem )
		{
			$prettyListItem    = $this->prettifier->prettify( $listItem );
			$prettyListItems[] = sprintf(
				"Element %d:\n%s\n%s",
				$index,
				str_repeat( '=', 9 + strlen( (string)$index ) ),
				$prettyListItem
			);
		}

		$rawKeyData = implode( "\n\n---\n\n", $rawListItems );
		$keyData    = implode( "\n\n---\n\n", $prettyListItems );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getKeyDataForWholeSet( ProvidesKeyInfo $keyInfo ) : ProvidesKeyData
	{
		$rawMembers = [];
		foreach ( $keyInfo->getSubItems() as $index => $member )
		{
			$rawMembers[] = sprintf(
				"Member %d:\n%s\n%s",
				$index,
				str_repeat( '=', 8 + strlen( (string)$index ) ),
				$member
			);
		}

		$prettyMembers = [];
		foreach ( $keyInfo->getSubItems() as $index => $member )
		{
			$prettyMember    = $this->prettifier->prettify( $member );
			$prettyMembers[] = sprintf(
				"Member %d:\n%s\n%s",
				$index,
				str_repeat( '=', 8 + strlen( (string)$index ) ),
				$prettyMember
			);
		}

		$rawKeyData = implode( "\n\n---\n\n", $rawMembers );
		$keyData    = implode( "\n\n---\n\n", $prettyMembers );

		return new KeyData( $keyData, $rawKeyData );
	}

	/**
	 * @param ProvidesKeyInfo $keyInfo
	 *
	 * @return ProvidesKeyData
	 */
	private function getKeyDataForWholeSortedSet( ProvidesKeyInfo $keyInfo ) : ProvidesKeyData
	{
		$setMembers = $keyInfo->getSubItems();
		$rawMembers = [];
		$i          = 0;
		foreach ( $setMembers as $member => $score )
		{
			$rawMembers[] = sprintf(
				"Member %d (Score: %s):\n%s\n%s",
				$i,
				(string)$score,
				str_repeat( '=', 18 + strlen( (string)$i ) + strlen( (string)$score ) ),
				$member
			);
			$i++;
		}

		$prettyMembers = [];
		$i             = 0;
		foreach ( $setMembers as $member => $score )
		{
			$prettyMember    = $this->prettifier->prettify( $member );
			$prettyMembers[] = sprintf(
				"Member %d (Score: %s):\n%s\n%s",
				$i,
				(string)$score,
				str_repeat( '=', 18 + strlen( (string)$i ) + strlen( (string)$score ) ),
				$prettyMember
			);
			$i++;
		}

		$rawKeyData = implode( "\n\n---\n\n", $rawMembers );
		$keyData    = implode( "\n\n---\n\n", $prettyMembers );

		return new KeyData( $keyData, $rawKeyData );
	}

	/**
	 * @param ServerManager   $manager
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @return ProvidesKeyData
	 */
	private function getKeyDataForString( ServerManager $manager, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$rawKeyData = $manager->getValue( $keyName->getKeyName() ) ?: '';
		$keyData    = $this->prettifier->prettify( $rawKeyData );

		return new KeyData( $keyData, $rawKeyData );
	}
}
