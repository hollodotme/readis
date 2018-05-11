<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\FinalResponders;

use hollodotme\Readis\Exceptions\RuntimeException;
use hollodotme\Readis\TwigPage;
use IceHawk\IceHawk\Constants\HttpCode;
use IceHawk\IceHawk\Interfaces\ProvidesReadRequestData;
use IceHawk\IceHawk\Interfaces\RespondsFinallyToReadRequest;

final class FinalReadResponder implements RespondsFinallyToReadRequest
{
	/**
	 * @param \Throwable              $throwable
	 * @param ProvidesReadRequestData $request
	 *
	 * @throws RuntimeException
	 */
	public function handleUncaughtException( \Throwable $throwable, ProvidesReadRequestData $request )
	{
		$data = [
			'errorMessage' => $throwable->getMessage(),
		];

		(new TwigPage())->respond( 'Theme/Error.twig', $data, HttpCode::INTERNAL_SERVER_ERROR );
	}
}