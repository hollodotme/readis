<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus\Configs;

/**
 * Class AppConfig
 *
 * @package hollodotme\RedisStatus\Configs
 */
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