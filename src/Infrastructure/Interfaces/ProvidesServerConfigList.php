<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Interfaces;

interface ProvidesServerConfigList
{
	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs() : array;

	public function getServerConfig( string $serverKey ) : ProvidesServerConfig;
}
