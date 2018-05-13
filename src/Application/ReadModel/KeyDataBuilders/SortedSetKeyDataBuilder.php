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

final class SortedSetKeyDataBuilder implements BuildsKeyData
{
	private const MEMBER_SEPARATOR = "\n\n---\n\n";

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
		return !$keyName->hasSubKey() && (KeyType::SORTED_SET === $keyInfo->getType());
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
		$members = $this->manager->getAllSortedSetMembers( $keyName->getKeyName() );

		$rawMembers = [];
		$index      = 0;
		foreach ( $members as $member => $score )
		{
			$rawMembers[] = $this->getMemberOutput( $index++, $member, $score );
		}

		$prettyMembers = [];
		$index         = 0;
		foreach ( $members as $member => $score )
		{
			$prettyMember    = $this->prettifier->prettify( $member );
			$prettyMembers[] = $this->getMemberOutput( $index++, $prettyMember, $score );
		}

		$rawKeyData = implode( self::MEMBER_SEPARATOR, $rawMembers );
		$keyData    = implode( self::MEMBER_SEPARATOR, $prettyMembers );

		return new KeyData( $keyData, $rawKeyData );
	}

	private function getMemberOutput( int $index, string $member, float $score ) : string
	{
		return sprintf(
			"Member %d (Score: %s):\n%s\n%s",
			$index,
			(string)$score,
			str_repeat( '=', 18 + strlen( (string)$index ) + strlen( (string)$score ) ),
			$member
		);
	}
}