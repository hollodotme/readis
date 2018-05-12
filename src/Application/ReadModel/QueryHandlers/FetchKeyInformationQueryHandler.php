<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyName;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\HashKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\HashSubKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\KeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\ListKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\ListSubKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SetKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SetSubKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SortedSetKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\SortedSetSubKeyDataBuilder;
use hollodotme\Readis\Application\ReadModel\KeyDataBuilders\StringKeyDataBuilder;
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

			$keyName = new KeyName( $query->getKeyName(), $query->getSubKey() );
			$keyData = $this->getKeyData( $manager, $keyInfo, $keyName );

			$result = new FetchKeyInformationResult();
			$result->setKeyData( $keyData );
			$result->setKeyInfo( $keyInfo );

			return $result;
		}
		catch ( ServerConfigNotFound | KeyTypeNotImplemented $e )
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
	 * @throws KeyTypeNotImplemented
	 * @return ProvidesKeyData
	 */
	private function getKeyData(
		ServerManager $manager,
		ProvidesKeyInfo $keyInfo,
		ProvidesKeyName $keyName
	) : ProvidesKeyData
	{
		$keyDataBuilder = new KeyDataBuilder(
			new HashKeyDataBuilder( $manager, $this->prettifier ),
			new HashSubKeyDataBuilder( $manager, $this->prettifier ),
			new ListKeyDataBuilder( $manager, $this->prettifier ),
			new ListSubKeyDataBuilder( $manager, $this->prettifier ),
			new SetKeyDataBuilder( $manager, $this->prettifier ),
			new SetSubKeyDataBuilder( $manager, $this->prettifier ),
			new SortedSetKeyDataBuilder( $manager, $this->prettifier ),
			new SortedSetSubKeyDataBuilder( $manager, $this->prettifier ),
			new StringKeyDataBuilder( $manager, $this->prettifier )
		);

		return $keyDataBuilder->buildKeyData( $keyInfo, $keyName );
	}
}
