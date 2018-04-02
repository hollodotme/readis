<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\StringUnserializers;

use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;

final class PrettifierChain implements PrettifiesString
{
	/** @var array|PrettifiesString[] */
	private $unserializers = [];

	public function addUnserializers( PrettifiesString ...$unserializers ) : void
	{
		foreach ( $unserializers as $unserializer )
		{
			$this->unserializers[] = $unserializer;
		}
	}

	public function canPrettify( string $data ) : bool
	{
		return true;
	}

	public function prettify( string $data ) : string
	{
		foreach ( $this->unserializers as $unserializer )
		{
			if ( $unserializer->canPrettify( $data ) )
			{
				return $unserializer->prettify( $data );
			}
		}

		return $data;
	}
}
