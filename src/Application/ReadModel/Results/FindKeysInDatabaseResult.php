<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Results;

use hollodotme\Readis\Interfaces\ProvidesKeyInformation;

final class FindKeysInDatabaseResult extends AbstractResult
{
	/** @var array|ProvidesKeyInformation[] */
	private $keyInfoObjects;

	public function getKeyInfoObjects() : array
	{
		return $this->keyInfoObjects;
	}

	public function setKeyInfoObjects( ProvidesKeyInformation ...$keyInfoObjects ) : void
	{
		$this->keyInfoObjects = $keyInfoObjects;
	}
}
