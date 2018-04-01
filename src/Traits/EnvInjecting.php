<?php declare(strict_types=1);

namespace hollodotme\Readis\Traits;

use hollodotme\Readis\Interfaces\ProvidesInfrastructure;

trait EnvInjecting
{
	/** @var ProvidesInfrastructure */
	private $env;

	public function __construct( ProvidesInfrastructure $env )
	{
		$this->env = $env;
	}

	final protected function getEnv() : ProvidesInfrastructure
	{
		return $this->env;
	}
}
