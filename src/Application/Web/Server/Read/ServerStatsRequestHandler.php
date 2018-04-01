<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Application\Web\Responses\EventSourceStream;
use hollodotme\Readis\Exceptions\LogicException;
use hollodotme\Readis\Exceptions\ServerConfigNotFound;
use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use function ignore_user_abort;
use function usleep;

final class ServerStatsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws ServerConfigNotFound
	 * @throws ConnectionFailedException
	 * @throws LogicException
	 */
	public function handle( ProvidesReadRequestData $request )
	{
		ignore_user_abort( false );

		$input            = $request->getInput();
		$serverKey        = (string)$input->get( 'serverKey', '0' );
		$serverConfigList = $this->getEnv()->getServerConfigList();
		$serverConfig     = $serverConfigList->getServerConfig( $serverKey );
		$serverManager    = $this->getEnv()->getServerManager( $serverConfig );

		$stream = new EventSourceStream();
		$stream->beginStream();

		while ( true )
		{
			$serverInfo = $serverManager->getServerInfo();

			$stream->streamEvent( (string)$serverInfo['connected_clients'], 'clientsConnected' );
			$stream->streamEvent( (string)$serverInfo['instantaneous_input_kbps'], 'inputKbPerSecond' );
			$stream->streamEvent( (string)$serverInfo['instantaneous_output_kbps'], 'outputKbPerSecond' );

			usleep( 500000 );
		}

		$stream->endStream();
	}
}
