<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Interfaces;

use hollodotme\Readis\Exceptions\ServerConfigNotFound;

interface ProvidesServerConfigList
{
	/**
	 * @return array|ProvidesServerConfig[]
	 */
	public function getServerConfigs() : array;

	/**
	 * @param string $serverKey
	 *
	 * @return ProvidesServerConfig
	 * @throws ServerConfigNotFound
	 */
	public function getServerConfig( string $serverKey ) : ProvidesServerConfig;
}
