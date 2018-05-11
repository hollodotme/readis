<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Interfaces;

interface ProvidesKeyData
{
	public function getKeyData() : string;

	public function getRawKeyData() : string;
}