<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Interfaces;

interface ProvidesHashKeyNames extends ProvidesKeyName
{
	public function getHashKeyName() : string;
}