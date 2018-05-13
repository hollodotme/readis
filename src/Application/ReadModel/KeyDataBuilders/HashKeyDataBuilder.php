<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\Interfaces\ProvidesRedisData;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

final class HashKeyDataBuilder implements BuildsKeyData
{
	private const FIELD_SEPARATOR = "\n\n---\n\n";

	/** @var ProvidesRedisData */
	private $manager;

	/** @var PrettifiesString */
	private $prettifier;

	public function __construct( ProvidesRedisData $manager, PrettifiesString $prettifier )
	{
		$this->manager    = $manager;
		$this->prettifier = $prettifier;
	}

	public function canBuildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : bool
	{
		return !$keyName->hasSubKey() && (KeyType::HASH === $keyInfo->getType());
	}

	/**
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws ConnectionFailedException
	 * @return ProvidesKeyData
	 */
	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$rawFields  = [];
		$hashValues = $this->manager->getAllHashValues( $keyName->getKeyName() );

		foreach ( $hashValues as $hashKey => $hashValue )
		{
			$rawFields[] = $this->getFieldOutput( $hashKey, $hashValue );
		}

		$prettyFields = [];
		foreach ( $hashValues as $hashKey => $hashValue )
		{
			$prettyHashValue = $this->prettifier->prettify( $hashValue );
			$prettyFields[]  = $this->getFieldOutput( $hashKey, $prettyHashValue );
		}

		$rawKeyData = implode( self::FIELD_SEPARATOR, $rawFields );
		$keyData    = implode( self::FIELD_SEPARATOR, $prettyFields );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getFieldOutput( string $hashKey, string $hashValue ) : string
	{
		return sprintf(
			"Field %s:\n%s\n%s",
			$hashKey,
			str_repeat( '=', 7 + strlen( (string)$hashKey ) ),
			$hashValue
		);
	}
}