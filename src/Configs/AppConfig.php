<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Configs;

/**
 * Class AppConfig
 *
 * @package hollodotme\Readis\Configs
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
