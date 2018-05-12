<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Queries;

final class FetchKeyInformationQuery
{
	/** @var int */
	private $database;

	/** @var string */
	private $keyName;

	/** @var null|string */
	private $subKey;

	public function __construct( int $database, string $keyName, ?string $subKey )
	{
		$this->database = $database;
		$this->keyName  = $keyName;
		$this->subKey   = $subKey;
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
