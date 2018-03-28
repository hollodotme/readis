<?php declare(strict_types=1);

namespace hollodotme\Readis\Traits;

use hollodotme\Readis\Env;

trait EnvInjecting
{
	/** @var Env */
	private $env;

	public function __construct( Env $env )
	{
		$this->env = $env;
	}

	final protected function getEnv() : Env
	{
		return $this->env;
	}
}
