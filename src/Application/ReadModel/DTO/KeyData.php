<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\DTO;

use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;

final class KeyData implements ProvidesKeyData
{
	/** @var string */
	private $keyData;

	/** @var string */
	private $rawKeyData;

	/** @var null|float */
	private $score;

	public function __construct( string $keyData, string $rawKeyData, ?float $score = null )
	{
		$this->keyData    = $keyData;
		$this->rawKeyData = $rawKeyData;
		$this->score      = $score;
	}

	public function getKeyData() : string
	{
		return $this->keyData;
	}

	public function getRawKeyData() : string
	{
		return $this->rawKeyData;
	}

	public function hasScore() : bool
	{
		return (null !== $this->score);
	}

	public function getScore() : ?float
	{
		return $this->score;
	}

}