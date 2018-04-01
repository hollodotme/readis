<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Queries;

final class FetchKeyInformationQuery
{
	/** @var string */
	private $serverKey;

	/** @var int */
	private $database;

	/** @var string */
	private $keyName;

	/** @var null|string */
	private $hashKey;

	public function __construct( string $serverKey, int $database, string $keyName, ?string $hashKey )
	{
		$this->serverKey = $serverKey;
		$this->database  = $database;
		$this->keyName   = $keyName;
		$this->hashKey   = $hashKey;
	}

	public function getServerKey() : string
	{
		return $this->serverKey;
	}

	public function getDatabase() : int
	{
		return $this->database;
	}

	public function getKeyName() : string
	{
		return $this->keyName;
	}

	public function getHashKey() : ?string
	{
		return $this->hashKey;
	}
}
