<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchKeyInformationQueryHandler;
use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Application\Web\Responses\TwigPage;
use hollodotme\Readis\Exceptions\RuntimeException;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use function base64_decode;

final class AjaxKeyDetailsRequestHandler extends AbstractRequestHandler implements HandlesGetRequest
{
	/**
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handle( ProvidesReadRequestData $request ) : void
	{
		$input     = $request->getInput();
		$serverKey = (string)$input->get( 'serverKey', '0' );
		$key       = base64_decode( (string)$input->get( 'keyName' ) );
		$subKey    = base64_decode( $input->get( 'subKey', '' ) );
		if ( '' === $subKey )
		{
			$subKey = null;
		}
		$database = (int)$input->get( 'database', 0 );
		$manager  = $this->getEnv()->getServerManagerForServerKey( $serverKey );

		$query  = new FetchKeyInformationQuery( $database, $key, $subKey );
		$result = (new FetchKeyInformationQueryHandler( $manager ))->handle( $query );

		if ( $result->failed() )
		{
			$data = ['errorMessage' => $result->getMessage()];
			(new TwigPage())->respond( 'Theme/Error.twig', $data, 500 );

			return;
		}

		$data = [
			'keyData' => $result->getKeyData(),
			'keyInfo' => $result->getKeyInfo(),
			'subKey'  => $subKey,
		];

		(new TwigPage())->respond( 'Server/Read/Pages/Includes/KeyData.twig', $data );
	}
}
