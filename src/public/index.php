<?php
/**
 * Redis Status
 *
 * @license MIT
 * @author  hollodotme
 */

namespace hollodotme\RedisStatus;

use hollodotme\RedisStatus\Configs\ServersConfig;

require(__DIR__ . '/../../vendor/autoload.php');

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

try
{
	$servers = new ServersConfig();

	if ( !isset($_REQUEST['server']) )
	{
		$page = new TwigPage(
			'ServerSelection.twig',
			[
				'servers' => $servers->getServerConfigs(),
			]
		);
		$page->respond();
	}
	else
	{
		$serverIndex  = intval( $_REQUEST['server'] );
		$serverConfig = $servers->getServerConfigs()[ $serverIndex ];
		$connection   = new ServerConnection( $serverConfig );
		$manager      = new ServerManager( $connection );

		$page = new TwigPage(
			'ServerInfo.twig',
			[
				'server'        => $serverConfig,
				'serverConfig'  => $manager->getServerConfig(),
				'slowLogLength' => $manager->getSlowLogLength(),
				'slowLogs'      => $manager->getSlowLogs(),
				'serverInfo'    => $manager->getServerInfo(),
			]
		);
		$page->respond();
	}
}
catch ( \Exception $e )
{
	$page = new TwigPage( 'Error.twig', [ 'errorName' => get_class( $e ), 'error' => $e ] );
	$page->respond();
}
