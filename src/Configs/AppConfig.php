<?php declare(strict_types=1);

namespace hollodotme\Readis\Configs;

final class AppConfig
{
	/** @var array */
	private $configData;

	public function __construct()
	{
		$this->configData = include(__DIR__ . '/../../config/app.php');
	}

	/**
	 * @return string
	 */
	public function getBaseUrl()
	{
		return $this->configData['baseUrl'];
	}
}
