<?php declare(strict_types=1);

namespace hollodotme\Readis;

abstract class AbstractObjectPool
{
	/** @var array */
	private $pool = [];

	final protected function getSharedInstance( string $name, \Closure $createFunction )
	{
		if ( !isset( $this->pool[ $name ] ) )
		{
			$this->pool[ $name ] = $createFunction->call( $this );
		}

		return $this->pool[ $name ];
	}
}
