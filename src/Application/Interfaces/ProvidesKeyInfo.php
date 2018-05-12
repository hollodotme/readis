<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Interfaces;

interface ProvidesKeyInfo
{
	public function getName() : string;

	public function getType() : string;

	public function getTimeToLive() : float;

	public function getSubItems() : array;

	public function countSubItems() : int;
}
