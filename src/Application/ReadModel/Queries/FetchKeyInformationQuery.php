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
	private $subKey;

	public function __construct( string $serverKey, int $database, string $keyName, ?string $subKey )
	{
		$this->serverKey = $serverKey;
		$this->database  = $database;
		$this->keyName   = $keyName;
		$this->subKey    = $subKey;
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

	public function getSubKey() : ?string
	{
		return $this->subKey;
	}
}
