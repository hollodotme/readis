<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Queries;

final class FindKeysInDatabaseQuery
{
	/** @var int */
	private $database;

	/** @var string */
	private $searchPattern;

	/** @var null|int */
	private $limit;

	public function __construct( int $database, string $searchPattern, ?int $limit )
	{
		$this->database      = $database;
		$this->searchPattern = $searchPattern;
		$this->limit         = $limit;
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
