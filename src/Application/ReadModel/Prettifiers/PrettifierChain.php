<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Prettifiers;

use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;

final class PrettifierChain implements PrettifiesString
{
	/** @var array|PrettifiesString[] */
	private $prettifiers = [];

	public function addPrettifiers( PrettifiesString ...$prettifiers ) : void
	{
		foreach ( $prettifiers as $prettifier )
		{
			$this->prettifiers[] = $prettifier;
		}
	}

	public function canPrettify( string $data ) : bool
	{
		return true;
	}

	public function prettify( string $data ) : string
	{
		foreach ( $this->prettifiers as $prettifier )
		{
			if ( $prettifier->canPrettify( $data ) )
			{
				return $prettifier->prettify( $data );
			}
		}

		return $data;
	}
}
