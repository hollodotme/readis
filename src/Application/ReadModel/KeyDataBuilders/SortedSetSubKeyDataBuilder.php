<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;

final class SortedSetSubKeyDataBuilder implements BuildsKeyData
{
	/** @var ServerManager */
	private $manager;

	/** @var PrettifiesString */
	private $prettifier;

	public function __construct( ServerManager $manager, PrettifiesString $prettifier )
	{
		$this->manager    = $manager;
		$this->prettifier = $prettifier;
	}

	public function canBuildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : bool
	{
		return $keyName->hasSubKey() && (KeyType::SORTED_SET === $keyInfo->getType());
	}

	/**
	 * @param ProvidesKeyInfo $keyInfo
	 * @param ProvidesKeyName $keyName
	 *
	 * @throws RuntimeException
	 * @return ProvidesKeyData
	 */
	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$members = $this->manager->getAllSortedSetMembers( $keyName->getKeyName() );
		$i       = 0;

		foreach ( $members as $member => $score )
		{
			if ( (int)$keyName->getSubKey() !== $i++ )
			{
				continue;
			}

			$rawKeyData = $member;
			$keyData    = $this->prettifier->prettify( $member );

			return new KeyData( $keyData, $rawKeyData, $score );
		}

		throw new RuntimeException( 'Could not find key in sorted set anymore.' );
	}
}