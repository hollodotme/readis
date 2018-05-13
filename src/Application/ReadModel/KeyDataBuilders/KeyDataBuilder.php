<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Exceptions\KeyTypeNotImplemented;
use function array_merge;

final class KeyDataBuilder implements BuildsKeyData
{
	/** @var array|BuildsKeyData[] */
	private $builders;

	public function __construct( BuildsKeyData $builder, BuildsKeyData ...$builders )
	{
		$this->builders = array_merge( [$builder], $builders );
	}

	public function canBuildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : bool
	{
		return true;
	}

	/**
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws KeyTypeNotImplemented
	 * @return ProvidesKeyData
	 */
	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		foreach ( $this->builders as $builder )
		{
			if ( $builder->canBuildKeyData( $keyInfo, $keyName ) )
			{
				return $builder->buildKeyData( $keyInfo, $keyName );
			}
		}

		throw new KeyTypeNotImplemented( 'Key type not implemented or supported: ' . $keyInfo->getType() );
	}
}