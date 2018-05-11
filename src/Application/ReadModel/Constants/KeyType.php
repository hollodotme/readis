<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Constants;

abstract class KeyType
{
	public const HASH   = 'hash';

	public const LIST   = 'list';

	public const SET    = 'set';

	public const STRING = 'string';
}