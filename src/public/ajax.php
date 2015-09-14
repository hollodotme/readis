<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus;

use hollodotme\RedisStatus\Configs\ServersConfig;
use hollodotme\RedisStatus\StringUnserializers\NullUnserializer;

require(__DIR__ . '/../../vendor/autoload.php');

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$servers      = new ServersConfig();
$serverIndex  = intval( $_REQUEST['server'] );
$serverConfig = $servers->getServerConfigs()[ $serverIndex ];
$connection   = new ServerConnection( $serverConfig );
$manager      = new ServerManager( $connection );

switch ( $_REQUEST['action'] )
{
	case 'getKeys':
	{
		$database = $_REQUEST['database'];
		$keyPattern = $_REQUEST['keyPattern'] ?: '*';

		$manager->selectDatabase( $database );
		$keyInfoObjects = $manager->getKeyInfoObjects( $keyPattern );

		$page = new TwigPage(
			'Includes/KeyList.twig',
			[
				'keyInfoObjects' => $keyInfoObjects,
				'database'       => $database,
				'serverIndex'    => $serverIndex,
			]
		);
		$page->respond();

		break;
	}

	case 'getKeyData':
	{
		$key      = $_REQUEST['key'];
		$database = $_REQUEST['database'];

		$manager->selectDatabase( $database );

		$keyData = $manager->getValueAsUnserializedString( $key, new NullUnserializer() );
		$keyInfo = $manager->getKeyInfoObject( $key );

		$page = new TwigPage(
			'Includes/KeyData.twig',
			[
				'keyData'     => $keyData,
				'keyInfo'     => $keyInfo,
				'database'    => $database,
				'serverIndex' => $serverIndex,
			]
		);
		$page->respond();

		break;
	}
}