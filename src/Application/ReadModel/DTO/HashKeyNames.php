<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesHashKeyNames;

final class HashKeyNames implements ProvidesHashKeyNames
{
	/** @var string */
	private $keyName;

	/** @var string */
	private $hashKeyName;

	public function __construct( string $keyName, string $hashKeyName )
	{
		$this->keyName     = $keyName;
		$this->hashKeyName = $hashKeyName;
	}

	public function getKeyName() : string
	{
		return $this->keyName;
	}

	public function getHashKeyName() : string
	{
		return $this->hashKeyName;
	}
}