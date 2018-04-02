<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Configs;

use const PHP_URL_PATH;

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
		return rtrim( (string)$this->configData['baseUrl'], '/' );
	}

	public function getBaseUri() : string
	{
		$baseUrl = $this->getBaseUrl();

		$path = parse_url( $baseUrl, PHP_URL_PATH ) ?? '';

		return $path !== false ? $path : $baseUrl;
	}
}
