<?php declare(strict_types=1);

namespace hollodotme\Readis\Interfaces;

interface UnserializesDataToString
{
	public function canUnserialize( string $data ) : bool;

	public function unserialize( string $data ) : string;
}
