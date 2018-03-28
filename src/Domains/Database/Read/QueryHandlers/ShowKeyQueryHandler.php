<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Database\Read\QueryHandlers;

use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Database\Read\Queries\ShowKeyQuery;
use hollodotme\Readis\ServerConnection;
use hollodotme\Readis\ServerManager;
use hollodotme\Readis\StringUnserializers\NullUnserializer;
use hollodotme\Readis\TwigPage;

/**
 * Class ShowKeyQueryHandler
 *
 * @package hollodotme\Readis\Domains\Database\Read\QueryHandlers
 */
final class ShowKeyQueryHandler
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
	 * @param ShowKeyQuery $query
	 *
	 * @throws \hollodotme\Readis\Exceptions\ServerConfigNotFound
	 */
	public function handle( ShowKeyQuery $query )
	{
		$serverKey = $query->getServerKey();
		$key       = $query->getKey();
		$hashKey   = $query->getHashKey();
		$database  = $query->getDatabase();

		$serverConfig = $this->serversConfig->getServerConfig( $serverKey );
		$connection   = new ServerConnection( $serverConfig );
		$manager      = new ServerManager( $connection );
		$manager->selectDatabase( $database );

		if ( empty($hashKey) )
		{
			$keyData = $manager->getValueAsUnserializedString( $key, new NullUnserializer() );
		}
		else
		{
			$keyData = $manager->getHashValueAsUnserializedString( $key, $hashKey, new NullUnserializer() );
		}

		$keyInfo = $manager->getKeyInfoObject( $key );

		$page = new TwigPage(
			'Includes/KeyData.twig',
			[
				'appConfig' => $this->appConfig,
				'keyData'   => $keyData,
				'keyInfo'   => $keyInfo,
				'database'  => $database,
				'serverKey' => $serverKey,
				'hashKey'   => $hashKey,
			]
		);

		$page->respond();
	}
}
