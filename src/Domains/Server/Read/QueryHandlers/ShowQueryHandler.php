<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Server\Read\QueryHandlers;

use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Server\Read\Queries\ShowQuery;
use hollodotme\Readis\ServerConnection;
use hollodotme\Readis\ServerManager;
use hollodotme\Readis\TwigPage;

/**
 * Class ShowQueryHandler
 *
 * @package hollodotme\Readis\Domains\Server\Read\QueryHandlers
 */
final class ShowQueryHandler
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
	 * @param ShowQuery $query
	 */
	public function handle( ShowQuery $query )
	{
		$serverConfig = $this->serversConfig->getServerConfig( $query->getServerKey() );

		$connection = new ServerConnection( $serverConfig );
		$manager    = new ServerManager( $connection );

		$page = new TwigPage(
			'ServerInfo.twig',
			[
				'appConfig'     => $this->appConfig,
				'server'        => $serverConfig,
				'database'      => '0',
				'serverKey'     => $query->getServerKey(),
				'databaseMap'   => $serverConfig->getDatabaseMap(),
				'serverConfig'  => $manager->getServerConfig(),
				'slowLogLength' => $manager->getSlowLogLength(),
				'slowLogs'      => $manager->getSlowLogs(),
				'serverInfo'    => $manager->getServerInfo(),
				'manager'       => $manager,
			]
		);

		$page->respond();
	}
}
