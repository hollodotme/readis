<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Configs;

final class AppConfig
{
	/** @var array */
	private $configData;

	public function __construct()
	{
		$this->configData = include __DIR__ . '/../../../config/app.php';
	}

	public function getBaseUrl() : string
	{
		return (string)$this->configData['baseUrl'];
	}
}
