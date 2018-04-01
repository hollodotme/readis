<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Responses;

use hollodotme\Readis\Exceptions\LogicException;

/**
 * Class EventSourceStream
 * @package Fortuneglobe\Spacenet\Cronjobs\Application\Web\Responses
 */
final class EventSourceStream
{
	private const BEGIN_OF_STREAM_EVENT = 'beginOfStream';

	private const END_OF_STREAM_EVENT   = 'endOfStream';

	/** @var int */
	private $eventSequence = 0;

	/** @var bool */
	private $active = false;

	/**
	 * @param bool $flushBuffer
	 *
	 * @throws LogicException
	 */
	public function beginStream( bool $flushBuffer = true ) : void
	{
		$this->active = true;

		header( 'Content-Type: text/event-stream; charset=utf-8' );

		if ( $flushBuffer )
		{
			@ob_end_flush();
			@ob_end_clean();
		}

		@ob_implicit_flush( 1 );

		$this->streamEvent( '', self::BEGIN_OF_STREAM_EVENT );
	}

	/**
	 * @param string      $data
	 * @param null|string $eventName
	 *
	 * @throws LogicException
	 */
	public function streamEvent( string $data, ?string $eventName = null ) : void
	{
		$this->guardStreamIsActive();

		if ( false !== strpos( $data, PHP_EOL ) )
		{
			foreach ( explode( PHP_EOL, $data ) as $line )
			{
				$this->streamEvent( $line, $eventName );
			}

			return;
		}

		echo 'id: ' . ++$this->eventSequence . PHP_EOL;
		echo (null !== $eventName) ? ('event: ' . $eventName . PHP_EOL) : '';

		echo 'data: ' . $data . PHP_EOL . PHP_EOL;
	}

	/**
	 * @throws LogicException
	 */
	private function guardStreamIsActive() : void
	{
		if ( !$this->active )
		{
			throw new LogicException( 'Event source stream is not active.' );
		}
	}

	/**
	 * @throws LogicException
	 */
	public function endStream() : void
	{
		$this->guardStreamIsActive();

		$this->streamEvent( '', self::END_OF_STREAM_EVENT );

		$this->active = false;

		@ob_implicit_flush( 0 );
	}
}
