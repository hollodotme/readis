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
use hollodotme\Readis\Domains\Database\Read\Queries\ShowKeyQuery;
use hollodotme\Readis\Domains\Database\Read\QueryHandlers\ShowKeyQueryHandler;

/**
 * Class AjaxShowKeyRequestHandler
 *
 * @package hollodotme\Readis\Domains\Database\Read
 */
final class AjaxShowKeyRequestHandler extends GetRequestHandler
{
	/**
	 * @param ServesGetRequestData $request
	 */
	public function handle( ServesGetRequestData $request )
	{
		$serversConfig = new ServersConfig();
		$appConfig     = new AppConfig();

		$query   = new ShowKeyQuery( $request );
		$handler = new ShowKeyQueryHandler( $serversConfig, $appConfig );

		$handler->handle( $query );
	}
}
