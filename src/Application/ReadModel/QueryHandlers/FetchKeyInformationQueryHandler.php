<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\QueryHandlers;

use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Constants\ResultType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyName;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
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
use hollodotme\Readis\Env;
use hollodotme\Readis\Exceptions\KeyTypeNotImplemented;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class FetchKeyInformationQueryHandler
{
	/** @var ProvidesRedisData */
	private $manager;

	/** @var BuildsKeyData */
	private $keyDataBuilder;

	public function __construct( ProvidesRedisData $manager )
	{
		$this->manager = $manager;

		$prettifier = new PrettifierChain();
		$configData = Env::instance()->getAppConfig()->getConfigData();
		$prettifiers = $configData['prettifiers'] ?? [
				JsonPrettifier::class,
				HyperLogLogPrettifier::class,
			];
		foreach ($prettifiers as $class) {
			$prettifier->addPrettifiers(new $class);
		}

		$this->keyDataBuilder = new KeyDataBuilder(
			new HashKeyDataBuilder( $manager, $prettifier ),
			new HashSubKeyDataBuilder( $manager, $prettifier ),
			new ListKeyDataBuilder( $manager, $prettifier ),
			new ListSubKeyDataBuilder( $manager, $prettifier ),
			new SetKeyDataBuilder( $manager, $prettifier ),
			new SetSubKeyDataBuilder( $manager, $prettifier ),
			new SortedSetKeyDataBuilder( $manager, $prettifier ),
			new SortedSetSubKeyDataBuilder( $manager, $prettifier ),
			new StringKeyDataBuilder( $manager, $prettifier )
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
			$this->manager->selectDatabase( $query->getDatabase() );

			$keyInfo = $this->manager->getKeyInfoObject( $query->getKeyName() );
			$keyName = new KeyName( $query->getKeyName(), $query->getSubKey() );
			$keyData = $this->keyDataBuilder->buildKeyData( $keyInfo, $keyName );

			$result = new FetchKeyInformationResult();
			$result->setKeyData( $keyData );
			$result->setKeyInfo( $keyInfo );

			return $result;
		}
		catch ( KeyTypeNotImplemented $e )
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
}
