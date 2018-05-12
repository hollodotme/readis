<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyName;

final class KeyName implements ProvidesKeyName
{
	/** @var string */
	private $keyName;

	/** @var null|string */
	private $subKey;

	public function __construct( string $keyName, ?string $subKey = null )
	{
		$this->keyName = $keyName;
		$this->subKey  = $subKey;
	}

	public function getKeyName() : string
	{
		return $this->keyName;
	}

	public function hasSubKey() : bool
	{
		return (null !== $this->subKey);
	}

	public function getSubKey() : ?string
	{
		return $this->subKey;
	}
}