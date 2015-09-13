<?php
/**
 *
 * @author hollodotme
 */

namespace hollodotme\RedisStatus;

use hollodotme\RedisStatus\Configs\ServersConfig;

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
	case 'dumpKeys':
	{
		$database   = $_REQUEST['keydb'];
		$keyPattern = $_REQUEST['keyPattern'] ?: '*';

		$dumpedKeys = $manager->dumpKeys( $database, $keyPattern );

		$page = new TwigPage(
			'Includes/KeyValueTable.twig',
			[
				'keyValues'    => $dumpedKeys,
				'keyCaption'   => 'Key',
				'valueCaption' => 'Value',
			]
		);
		$page->respond();
	}
}