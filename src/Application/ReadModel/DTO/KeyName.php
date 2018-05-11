<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;

final class KeyName implements ProvidesKeyName
{
	/** @var string */
	private $keyName;

	public function __construct( string $keyName )
	{
		$this->keyName = $keyName;
	}

	public function getKeyName() : string
	{
		return $this->keyName;
	}
}