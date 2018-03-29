<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\StringUnserializers;

use hollodotme\Readis\Interfaces\UnserializesDataToString;

final class UnserializerChain implements UnserializesDataToString
{
	/** @var array|UnserializesDataToString[] */
	private $unserializers = [];

	public function addUnserializers( UnserializesDataToString ...$unserializers ) : void
	{
		foreach ( $unserializers as $unserializer )
		{
			$this->unserializers[] = $unserializer;
		}
	}

	public function canUnserialize( string $data ) : bool
	{
		return true;
	}

	public function unserialize( string $data ) : string
	{
		foreach ( $this->unserializers as $unserializer )
		{
			if ( $unserializer->canUnserialize( $data ) )
			{
				return $unserializer->unserialize( $data );
			}
		}

		return $data;
	}
}
