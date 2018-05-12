<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Results;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;

final class FindKeysInDatabaseResult extends AbstractResult
{
	/** @var array|ProvidesKeyInfo[] */
	private $keyInfoObjects;

	/**
	 * @return array|ProvidesKeyInfo[]
	 */
	public function getKeyInfoObjects() : array
	{
		return $this->keyInfoObjects;
	}

	public function setKeyInfoObjects( ProvidesKeyInfo ...$keyInfoObjects ) : void
	{
		$this->keyInfoObjects = $keyInfoObjects;
	}
}
