<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Queries;

final class FetchServerInformationQuery
{
	/** @var string */
	private $serverKey;

	public function __construct( string $serverKey )
	{
		$this->serverKey = $serverKey;
	}

	public function getServerKey() : string
	{
		return $this->serverKey;
	}
}
