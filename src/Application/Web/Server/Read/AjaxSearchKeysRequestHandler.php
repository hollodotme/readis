<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\ReadModel\Queries\FindKeysInDatabaseQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FindKeysInDatabaseQueryHandler;
use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\TwigPage;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use function abs;

final class AjaxSearchKeysRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handle( ProvidesReadRequestData $request )
	{
		$input         = $request->getInput();
		$serverKey     = (string)$input->get( 'serverKey', '0' );
		$database      = (int)$input->get( 'database', 0 );
		$limit         = $this->getValidLimit( (string)$input->get( 'limit', '50' ) );
		$searchPattern = ((string)$input->get( 'searchPattern', '*' )) ?: '*';
		$appConfig     = $this->getEnv()->getAppConfig();

		$serverManager = $this->getEnv()->getServerManagerForServerKey( $serverKey );

		$query  = new FindKeysInDatabaseQuery( $database, $searchPattern, $limit );
		$result = (new FindKeysInDatabaseQueryHandler( $serverManager ))->handle( $query );

		if ( $result->failed() )
		{
			$data = ['errorMessage' => $result->getMessage()];
			(new TwigPage())->respond( 'Theme/Error.twig', $data, 500 );

			return;
		}

		$data = [
			'appConfig'      => $appConfig,
			'keyInfoObjects' => $result->getKeyInfoObjects(),
			'database'       => $database,
			'serverKey'      => $serverKey,
		];

		(new TwigPage())->respond( 'Server/Read/Pages/Includes/KeyList.twig', $data );
	}

	private function getValidLimit( string $limit ) : ?int
	{
		return ('all' === $limit) ? null : abs( (int)$limit );
	}
}
