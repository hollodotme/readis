<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Server\Read;

use Fortuneglobe\IceHawk\DomainRequestHandlers\GetRequestHandler;
use Fortuneglobe\IceHawk\Interfaces\ServesGetRequestData;
use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Server\Read\Queries\ShowQuery;
use hollodotme\Readis\Domains\Server\Read\QueryHandlers\ShowQueryHandler;

/**
 * Class ShowRequestHandler
 *
 * @package hollodotme\Readis\Domains\Server\Read
 */
final class ShowRequestHandler extends GetRequestHandler
{
	/**
	 * @param ServesGetRequestData $request
	 */
	public function handle( ServesGetRequestData $request )
	{
		$serversConfig = new ServersConfig();
		$appConfig     = new AppConfig();

		$query   = new ShowQuery( $request );
		$handler = new ShowQueryHandler( $serversConfig, $appConfig );

		$handler->handle( $query );
	}
}
