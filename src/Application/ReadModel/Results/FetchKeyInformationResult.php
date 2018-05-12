<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Results;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;
use hollodotme\Readis\Application\ReadModel\Interfaces\ProvidesKeyData;

final class FetchKeyInformationResult extends AbstractResult
{
	/** @var ProvidesKeyData */
	private $keyData;

	/** @var ProvidesKeyInfo */
	private $keyInfo;

	public function getKeyData() : ProvidesKeyData
	{
		return $this->keyData;
	}

	public function setKeyData( ProvidesKeyData $keyData ) : void
	{
		$this->keyData = $keyData;
	}

	public function getKeyInfo() : ProvidesKeyInfo
	{
		return $this->keyInfo;
	}

	public function setKeyInfo( ProvidesKeyInfo $keyInfo ) : void
	{
		$this->keyInfo = $keyInfo;
	}
}
