<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Queries;

final class FindKeysInDatabaseQuery
{
	/** @var string */
	private $serverKey;

	/** @var int */
	private $database;

	/** @var string */
	private $searchPattern;

	/** @var null|int */
	private $limit;

	public function __construct( string $serverKey, int $database, string $searchPattern, ?int $limit )
	{
		$this->serverKey     = $serverKey;
		$this->database      = $database;
		$this->searchPattern = $searchPattern;
		$this->limit         = $limit;
	}

	public function getServerKey() : string
	{
		return $this->serverKey;
	}

	public function getDatabase() : int
	{
		return $this->database;
	}

	public function getSearchPattern() : string
	{
		return $this->searchPattern;
	}

	public function getLimit() : ?int
	{
		return $this->limit;
	}
}
