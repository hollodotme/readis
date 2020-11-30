<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Web\Responses;

use hollodotme\Readis\Exceptions\LogicException;
use function flush;

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

	/** @var bool */
	private $flushBuffer = false;

	/**
	 * @param bool $flushBuffer
	 *
	 * @throws LogicException
	 */
	public function beginStream( bool $flushBuffer = true ) : void
	{
		$this->active      = true;
		$this->flushBuffer = $flushBuffer;

		header( 'Content-Type: text/event-stream; charset=utf-8' );
		header( 'Access-Control-Allow-Origin: *' );

		if ( $this->flushBuffer )
		{
			@ob_end_flush();
			@ob_end_clean();
		}

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

		if ( $this->flushBuffer )
		{
			flush();
		}
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

		if ( $this->flushBuffer )
		{
			flush();
		}

		$this->active = false;
	}
}
