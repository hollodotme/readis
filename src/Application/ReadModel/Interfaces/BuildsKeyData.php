<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Interfaces;

use hollodotme\Readis\Application\Interfaces\ProvidesKeyInfo;

interface BuildsKeyData
{
	public function canBuildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : bool;

	public function buildKeyData( ProvidesKeyInfo $keyInfo, ProvidesKeyName $keyName ) : ProvidesKeyData;
}