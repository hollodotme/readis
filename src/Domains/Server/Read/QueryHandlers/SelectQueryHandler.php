<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Server\Read\QueryHandlers;

use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Server\Read\Queries\SelectQuery;
use hollodotme\Readis\TwigPage;

/**
 * Class SelectQueryHandler
 *
 * @package hollodotme\Readis\Domains\Server\Read\QueryHandlers
 */
final class SelectQueryHandler
{
	/** @var ServersConfig */
	private $serversConfig;

	/** @var AppConfig */
	private $appConfig;

	/**
	 * @param ServersConfig $serversConfig
	 * @param AppConfig     $appConfig
	 */
	public function __construct( ServersConfig $serversConfig, AppConfig $appConfig )
	{
		$this->serversConfig = $serversConfig;
		$this->appConfig     = $appConfig;
	}

	/**
	 * @param SelectQuery $query
	 */
	public function handle( SelectQuery $query )
	{
		$page = new TwigPage(
			'ServerSelection.twig',
			[
				'appConfig' => $this->appConfig,
				'servers'   => $this->serversConfig->getServerConfigs(),
			]
		);

		$page->respond();
	}
}
