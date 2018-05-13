<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;

final class SetSubKeyDataBuilder implements BuildsKeyData
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
		return $keyName->hasSubKey() && (KeyType::SET === $keyInfo->getType());
	}

	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$rawKeyData = $keyInfo->getSubItems()[ (int)$keyName->getSubKey() ];
		$keyData    = $this->prettifier->prettify( $rawKeyData );

		return new KeyData( $keyData, $rawKeyData );
	}
}