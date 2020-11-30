<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\FinalResponders;

use hollodotme\Readis\Application\Web\Responses\TwigPage;
use hollodotme\Readis\Exceptions\RuntimeException;
use IceHawk\IceHawk\Constants\HttpCode;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use IceHawk\IceHawk\Interfaces\RespondsFinallyToReadRequest;
use Throwable;

final class FinalReadResponder implements RespondsFinallyToReadRequest
{
	/**
	 * @param Throwable               $throwable
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handleUncaughtException( Throwable $throwable, ProvidesReadRequestData $request ) : void
	{
		$data = [
			'errorMessage' => $throwable->getMessage(),
		];

		(new TwigPage())->respond( 'Theme/Error.twig', $data, HttpCode::INTERNAL_SERVER_ERROR );
	}
}