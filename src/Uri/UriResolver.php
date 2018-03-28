<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Uri;

use Fortuneglobe\IceHawk\Interfaces\ServesRequestInfo;
use Fortuneglobe\IceHawk\UriComponents;

/**
 * Class UriResolver
 *
 * @package hollodotme\Readis\Uri
 */
final class UriResolver extends \Fortuneglobe\IceHawk\UriResolver
{
	/**
	 * @param ServesRequestInfo $requestInfo
	 *
	 * @throws \Fortuneglobe\IceHawk\Exceptions\MalformedRequestUri
	 * @return \Fortuneglobe\IceHawk\Interfaces\ServesUriComponents
	 */
	public function resolveUri( ServesRequestInfo $requestInfo )
	{
		$uri = $requestInfo->getUri();

		# Show server selection
		if ( $uri == '/' )
		{
			return new UriComponents( 'server', 'select', [ ] );
		}

		# Show a specific server
		if ( preg_match( "#^/server/([0-9]+)/?$#", $uri, $matches ) )
		{
			return new UriComponents( 'server', 'show', [ 'serverKey' => $matches[1] ] );
		}

		return parent::resolveUri( $requestInfo );
	}
}
