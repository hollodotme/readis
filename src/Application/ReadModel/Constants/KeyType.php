<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Constants;

abstract class KeyType
{
	public const HASH       = 'hash';

	public const LIST       = 'list';

	public const SET        = 'set';

	public const SORTED_SET = 'zset';

	public const STRING     = 'string';
}