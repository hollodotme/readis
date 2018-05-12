<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Prettifiers;

use hollodotme\Readis\Application\ReadModel\Interfaces\PrettifiesString;
use function stripos;

final class HyperLogLogPrettifier implements PrettifiesString
{
	public function canPrettify( string $data ) : bool
	{
		return (0 === stripos( $data, 'HYLL' ));
	}

	public function prettify( string $data ) : string
	{
		return $data . "\n\n(HyperLogLog encoded value)";
	}
}