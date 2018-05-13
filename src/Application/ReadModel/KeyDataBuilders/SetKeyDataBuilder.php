<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\KeyDataBuilders;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Constants\KeyType;
use hollodotme\Readis\Application\ReadModel\DTO\KeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\BuildsKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use hollodotme\Readis\Infrastructure\Redis\ServerManager;

final class SetKeyDataBuilder implements BuildsKeyData
{
	private const MEMBER_SEPARATOR = "\n\n---\n\n";

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
		return !$keyName->hasSubKey() && (KeyType::SET === $keyInfo->getType());
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
		$members = $this->manager->getAllSetMembers( $keyName->getKeyName() );

		$rawMembers = [];
		foreach ( $members as $index => $member )
		{
			$rawMembers[] = $this->getMemberOutput( $index, $member );
		}

		$prettyMembers = [];
		foreach ( $members as $index => $member )
		{
			$prettyMember    = $this->prettifier->prettify( $member );
			$prettyMembers[] = $this->getMemberOutput( $index, $prettyMember );
		}

		$rawKeyData = implode( self::MEMBER_SEPARATOR, $rawMembers );
		$keyData    = implode( self::MEMBER_SEPARATOR, $prettyMembers );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getMemberOutput( int $index, string $member ) : string
	{
		return sprintf(
			"Member %d:\n%s\n%s",
			$index,
			str_repeat( '=', 8 + strlen( (string)$index ) ),
			$member
		);
	}
}