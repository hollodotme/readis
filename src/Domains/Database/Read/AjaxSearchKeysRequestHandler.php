<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Database\Read;

use Fortuneglobe\IceHawk\DomainRequestHandlers\GetRequestHandler;
use Fortuneglobe\IceHawk\Interfaces\ServesGetRequestData;
use hollodotme\Readis\Configs\AppConfig;
use hollodotme\Readis\Configs\ServersConfig;
use hollodotme\Readis\Domains\Database\Read\Queries\SearchKeysQuery;
use hollodotme\Readis\Domains\Database\Read\QueryHandlers\SearchKeysQueryHandler;

/**
 * Class AjaxSearchKeysRequestHandler
 *
 * @package hollodotme\Readis\Domains\Database\Read
 */
final class AjaxSearchKeysRequestHandler extends GetRequestHandler
{
	/**
	 * @param ServesGetRequestData $request
	 */
	public function handle( ServesGetRequestData $request )
	{
		$serversConfig = new ServersConfig();
		$appConfig     = new AppConfig();

		$query   = new SearchKeysQuery( $request );
		$handler = new SearchKeysQueryHandler( $serversConfig, $appConfig );

		$handler->handle( $query );
	}
}
