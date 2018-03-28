<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Configs;

use Fortuneglobe\IceHawk\Interfaces\ListensToEvents;
use Fortuneglobe\IceHawk\Interfaces\ResolvesUri;
use Fortuneglobe\IceHawk\Interfaces\RewritesUri;
use Fortuneglobe\IceHawk\Interfaces\ServesIceHawkConfig;
use Fortuneglobe\IceHawk\Interfaces\ServesRequestInfo;
use Fortuneglobe\IceHawk\RequestInfo;
use hollodotme\Readis\Uri\UriResolver;
use hollodotme\Readis\Uri\UriRewriter;

/**
 * Class IceHawkConfig
 *
 * @package hollodotme\Readis\Configs
 */
final class IceHawkConfig implements ServesIceHawkConfig
{
	/**
	 * @return string
	 */
	public function getDomainNamespace()
	{
		return 'hollodotme\\Readis\\Domains';
	}

	/**
	 * @return RewritesUri
	 */
	public function getUriRewriter()
	{
		return new UriRewriter();
	}

	/**
	 * @return ResolvesUri
	 */
	public function getUriResolver()
	{
		return new UriResolver();
	}

	/**
	 * @return array|ListensToEvents[]
	 */
	public function getEventListeners()
	{
		return [ ];
	}

	/**
	 * @return ServesRequestInfo
	 */
	public function getRequestInfo()
	{
		return RequestInfo::fromEnv();
	}

}
