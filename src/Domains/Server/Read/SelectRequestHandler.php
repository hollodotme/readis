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
use hollodotme\Readis\Domains\Server\Read\Queries\SelectQuery;
use hollodotme\Readis\Domains\Server\Read\QueryHandlers\SelectQueryHandler;

/**
 * Class SelectRequestHandler
 *
 * @package hollodotme\Readis\Domains\Server\Read
 */
final class SelectRequestHandler extends GetRequestHandler
{
	/**
	 * @param ServesGetRequestData $request
	 */
	public function handle( ServesGetRequestData $request )
	{
		$appConfig     = new AppConfig();
		$serversConfig = new ServersConfig();

		$query   = new SelectQuery( $request );
		$handler = new SelectQueryHandler( $serversConfig, $appConfig );

		$handler->handle( $query );
	}
}
