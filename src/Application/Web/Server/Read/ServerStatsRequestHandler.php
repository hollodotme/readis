<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Application\Web\Responses\EventSourceStream;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use function ignore_user_abort;
use function usleep;

final class ServerStatsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 */
	public function handle( ProvidesReadRequestData $request ) : void
	{
		ignore_user_abort( false );

		$input         = $request->getInput();
		$serverKey     = (string)$input->get( 'serverKey', '0' );
		$serverConfig  = $this->getEnv()->getServerConfigList()->getServerConfig( $serverKey );
		$serverManager = $this->getEnv()->getServerManager( $serverConfig );

		$stream = new EventSourceStream();
		$stream->beginStream();

		while ( true )
		{
			$serverInfo = $serverManager->getServerInfo();

			$stream->streamEvent( (string)$serverInfo['connected_clients'], 'clientsConnected' );
			$stream->streamEvent(
				sprintf(
					'%s:%s',
					(string)$serverInfo['instantaneous_input_kbps'],
					(string)$serverInfo['instantaneous_output_kbps']
				),
				'ioKbPerSecond'
			);

			usleep( 500000 );
		}

		/** @noinspection PhpUnreachableStatementInspection */
		$stream->endStream();
	}
}
