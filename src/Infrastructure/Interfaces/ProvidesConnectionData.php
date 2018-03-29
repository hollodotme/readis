<?php declare(strict_types=1);

namespace hollodotme\Readis\Infrastructure\Interfaces;

interface ProvidesConnectionData
{
	public function getHost() : string;

	public function getPort() : int;

	public function getTimeout() : float;

	public function getRetryInterval() : int;

	public function getAuth() : ?string;
}
