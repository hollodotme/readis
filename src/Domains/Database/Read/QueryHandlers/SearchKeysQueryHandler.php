<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Database\Read\QueryHandlers;

use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Database\Read\Queries\SearchKeysQuery;
use hollodotme\Readis\ServerConnection;
use hollodotme\Readis\ServerManager;
use hollodotme\Readis\TwigPage;

/**
 * Class SearchKeysQueryHandler
 *
 * @package hollodotme\Readis\Domains\Database\Read\QueryHandlers
 */
final class SearchKeysQueryHandler
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
	 * @param SearchKeysQuery $query
	 */
	public function handle( SearchKeysQuery $query )
	{
		$serverConfig  = $this->serversConfig->getServerConfig( $query->getServerKey() );
		$connection    = new ServerConnection( $serverConfig );
		$manager       = new ServerManager( $connection );
		$limit         = $this->getValidLimit( $query->getLimit() );
		$searchPattern = $query->getSearchPattern() ?: '*';

		$manager->selectDatabase( $query->getDatabase() );

		$keyInfoObjects = $manager->getKeyInfoObjects( $searchPattern, $limit );

		$page = new TwigPage(
			'Includes/KeyList.twig',
			[
				'appConfig'      => $this->appConfig,
				'keyInfoObjects' => $keyInfoObjects,
				'database'       => $query->getDatabase(),
				'serverKey'      => $query->getServerKey(),
			]
		);

		$page->respond();
	}

	/**
	 * @param string $limit
	 *
	 * @return null|int
	 */
	private function getValidLimit( $limit )
	{
		if ( $limit == 'all' )
		{
			$validLimit = null;
		}
		else
		{
			$validLimit = abs( intval( $limit ) );
		}

		return $validLimit;
	}
}
