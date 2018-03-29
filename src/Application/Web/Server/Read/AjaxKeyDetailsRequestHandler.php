<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Server\Read;

use hollodotme\Readis\Application\StringUnserializers\JsonPrettyfier;
use hollodotme\Readis\Application\StringUnserializers\UnserializerChain;
use hollodotme\Readis\Application\Web\AbstractRequestHandler;
use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\TwigPage;
use IceHawk\IceHawk\Interfaces\HandlesGetRequest;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;

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
		$key       = (string)$input->get( 'keyName' );
		$hashKey   = (string)$input->get( 'hashKey', '' );
		$database  = (int)$input->get( 'database', 0 );

		$appConfig        = $this->getEnv()->getAppConfig();
		$serverConfigList = $this->getEnv()->getServerConfigList();
		$serverConfig     = $serverConfigList->getServerConfig( $serverKey );
		$manager          = $this->getEnv()->getServerManager( $serverConfig );

		$manager->selectDatabase( $database );

		$unserializer = new UnserializerChain();
		$unserializer->addUnserializers( new JsonPrettyfier() );

		if ( empty( $hashKey ) )
		{
			$keyData = $manager->getValueAsUnserializedString( $key, $unserializer );
		}
		else
		{
			$keyData = $manager->getHashValueAsUnserializedString( $key, $hashKey, $unserializer );
		}

		$keyInfo = $manager->getKeyInfoObject( $key );

		$data = [
			'appConfig' => $appConfig,
			'keyData'   => $keyData,
			'keyInfo'   => $keyInfo,
			'database'  => $database,
			'serverKey' => $serverKey,
			'hashKey'   => $hashKey,
		];

		(new TwigPage())->respond( 'Server/Read/Pages/Includes/KeyData.twig', $data );
	}
}
