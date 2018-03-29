<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Interfaces;

interface ProvidesServerConfig
{
	public function getName() : string;

	public function getHost() : string;

	public function getPort() : int;

	public function getTimeout() : float;

	public function getRetryInterval() : int;

	public function getAuth() : ?string;

	public function getDatabaseMap() : array;
}
