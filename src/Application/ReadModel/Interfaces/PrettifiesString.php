<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Interfaces;

interface PrettifiesString
{
	public function canPrettify( string $data ) : bool;

	public function prettify( string $data ) : string;
}
