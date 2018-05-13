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

final class ListKeyDataBuilder implements BuildsKeyData
{
	private const ELEMENT_SEPARATOR = "\n\n---\n\n";

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
		return !$keyName->hasSubKey() && (KeyType::LIST === $keyInfo->getType());
	}

	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData
	{
		$rawElements = [];
		foreach ( $keyInfo->getSubItems() as $index => $element )
		{
			$rawElements[] = $this->getElementOutput( $index, $element );
		}

		$prettyElements = [];
		foreach ( $keyInfo->getSubItems() as $index => $element )
		{
			$prettyElement    = $this->prettifier->prettify( $element );
			$prettyElements[] = $this->getElementOutput( $index, $prettyElement );
		}

		$rawKeyData = implode( self::ELEMENT_SEPARATOR, $rawElements );
		$keyData    = implode( self::ELEMENT_SEPARATOR, $prettyElements );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getElementOutput( int $index, string $element ) : string
	{
		return sprintf(
			"Element %d:\n%s\n%s",
			$index,
			str_repeat( '=', 9 + strlen( (string)$index ) ),
			$element
		);
	}
}