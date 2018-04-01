<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Results;

use hollodotme\Readis\Application\ReadModel\DTO\ServerInformation;

final class FetchServerInformationResult extends AbstractResult
{
	/** @var ServerInformation */
	private $serverInformation;

	public function getServerInformation() : ServerInformation
	{
		return $this->serverInformation;
	}

	public function setServerInformation( ServerInformation $serverInformation ) : void
	{
		$this->serverInformation = $serverInformation;
	}
}
