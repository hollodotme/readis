<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;

final class KeyData implements ProvidesKeyData
{
	/** @var string */
	private $keyData;

	/** @var string */
	private $rawKeyData;

	public function __construct( string $keyData, string $rawKeyData )
	{
		$this->keyData    = $keyData;
		$this->rawKeyData = $rawKeyData;
	}

	public function getKeyData() : string
	{
		return $this->keyData;
	}

	public function getRawKeyData() : string
	{
		return $this->rawKeyData;
	}
}