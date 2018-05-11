<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Configs;

use hollodotme\Readis\Application\FinalResponders\FinalReadResponder;
use hollodotme\Readis\Application\Web\Server\Read\AjaxKeyDetailsRequestHandler;
use hollodotme\Readis\Application\Web\Server\Read\AjaxSearchKeysRequestHandler;
use hollodotme\Readis\Application\Web\Server\Read\ServerDetailsRequestHandler;
use hollodotme\Readis\Application\Web\Server\Read\ServerSelectionRequestHandler;
use hollodotme\Readis\Application\Web\Server\Read\ServerStatsRequestHandler;
use hollodotme\Readis\Traits\EnvInjecting;
use IceHawk\IceHawk\Defaults\Traits\DefaultCookieProviding;
use IceHawk\IceHawk\Defaults\Traits\DefaultEventSubscribing;
use IceHawk\IceHawk\Defaults\Traits\DefaultFinalWriteResponding;
use IceHawk\IceHawk\Defaults\Traits\DefaultRequestBypassing;
use IceHawk\IceHawk\Defaults\Traits\DefaultRequestInfoProviding;
use IceHawk\IceHawk\Defaults\Traits\DefaultWriteRouting;
use IceHawk\IceHawk\Interfaces\ConfiguresIceHawk;
use IceHawk\IceHawk\Interfaces\RespondsFinallyToReadRequest;
use IceHawk\IceHawk\Routing\Patterns\NamedRegExp;
use IceHawk\IceHawk\Routing\ReadRoute;
use function preg_quote;

final class IceHawkConfig implements ConfiguresIceHawk
{
	use EnvInjecting;
	use DefaultCookieProviding;
	use DefaultEventSubscribing;
	use DefaultFinalWriteResponding;
	use DefaultRequestBypassing;
	use DefaultRequestInfoProviding;
	use DefaultWriteRouting;

	/** @var array */
	private $readRoutes;

	public function getReadRoutes()
	{
		$this->buildReadRoutes();

		foreach ( $this->readRoutes as $pattern => $handlerClass )
		{
			yield new ReadRoute(
				new NamedRegExp( $pattern, 'i' ),
				new $handlerClass( $this->getEnv() )
			);
		}
	}

	private function buildReadRoutes() : void
	{
		if ( null !== $this->readRoutes )
		{
			return;
		}

		$baseUrl       = $this->getEnv()->getAppConfig()->getBaseUri();
		$quotedBaseUri = preg_quote( $baseUrl, '!' );

		$this->readRoutes = [
			'^' . $quotedBaseUri . '/?$'                                                                                            => ServerSelectionRequestHandler::class,
			'^' . $quotedBaseUri . '/server/(?<serverKey>\d+)/stats/?$'                                                             => ServerStatsRequestHandler::class,
			'^' . $quotedBaseUri . '/server/(?<serverKey>\d+)(?:/database/(?<database>\d+))?/?$'                                    => ServerDetailsRequestHandler::class,
			'^' . $quotedBaseUri . '/server/(?<serverKey>\d+)/database/(?<database>\d+)/keys/?$'                                    => AjaxSearchKeysRequestHandler::class,
			'^' . $quotedBaseUri . '/server/(?<serverKey>\d+)/database/(?<database>\d+)/keys/(?<keyName>.+)/hash/(?<hashKey>.+)/?$' => AjaxKeyDetailsRequestHandler::class,
			'^' . $quotedBaseUri . '/server/(?<serverKey>\d+)/database/(?<database>\d+)/keys/(?<keyName>.+)/?$'                     => AjaxKeyDetailsRequestHandler::class,
		];
	}

	public function getFinalReadResponder() : RespondsFinallyToReadRequest
	{
		return new FinalReadResponder();
	}
}
