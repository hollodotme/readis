<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\ReadModel\Queries\FetchKeyInformationQuery;
use hollodotme\Readis\Application\ReadModel\QueryHandlers\FetchKeyInformationQueryHandler;
use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\TwigPage;
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
	public function handle( ProvidesReadRequestData $request )
	{
		$input     = $request->getInput();
		$serverKey = (string)$input->get( 'serverKey', '0' );
		$key       = base64_decode( (string)$input->get( 'keyName' ) );
		$hashKey   = base64_decode( $input->get( 'hashKey', '' ) ) ?: null;
		$database  = (int)$input->get( 'database', 0 );

		$query  = new FetchKeyInformationQuery( $serverKey, $database, $key, $hashKey );
		$result = (new FetchKeyInformationQueryHandler( $this->getEnv() ))->handle( $query );

		if ( $result->failed() )
		{
			$data = ['errorMessage' => $result->getMessage()];
			(new TwigPage())->respond( 'Theme/Error.twig', $data, 500 );

			return;
		}

		$data = [
			'rawKeyData' => $result->getRawKeyData(),
			'keyData'    => $result->getKeyData(),
			'keyInfo'    => $result->getKeyInfo(),
			'hashKey'    => $hashKey,
		];

		(new TwigPage())->respond( 'Server/Read/Pages/Includes/KeyData.twig', $data );
	}
}
