<?php declare(strict_types=1);

namespace hollodotme\Readis\Tests\Unit\Application\Web\Responses;

use hollodotme\Readis\Application\Web\Responses\EventSourceStream;
use hollodotme\Readis\Exceptions\LogicException;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

final class EventSourceStreamTest extends TestCase
{
	/**
	 * @runInSeparateProcess
	 * @throws LogicException
	 * @throws Exception
	 * @throws ExpectationFailedException
	 */
	public function testCanBeginStream() : void
	{
		$stream = new EventSourceStream();
		$stream->beginStream( false );
		self::assertContains( 'Content-Type: text/event-stream; charset=utf-8', xdebug_get_headers() );
		$this->expectOutputString( "id: 1\nevent: beginOfStream\ndata: \n\n" );
	}

	/**
	 * @throws LogicException
	 */
	public function testEndingNonActiveStreamThrowsException() : void
	{
		$stream = new EventSourceStream();
		$this->expectException( LogicException::class );
		$stream->endStream();
	}

	/**
	 * @throws LogicException
	 */
	public function testStreamingAnEventOnNonActiveStreamThrowsException() : void
	{
		$stream = new EventSourceStream();
		$this->expectException( LogicException::class );
		$stream->streamEvent( 'Test' );
	}

	/**
	 * @runInSeparateProcess
	 * @throws LogicException
	 */
	public function testCanStreamEvents() : void
	{
		$stream         = new EventSourceStream();
		$expectedOutput = "id: 1\nevent: beginOfStream\ndata: \n\n";
		$expectedOutput .= "id: 2\ndata: Unit\n\n";
		$expectedOutput .= "id: 3\nevent: testEvent\ndata: Test\n\n";
		$expectedOutput .= "id: 4\nevent: testEvent\ndata: Unit\n\n";
		$expectedOutput .= "id: 5\nevent: endOfStream\ndata: \n\n";
		$stream->beginStream( false );
		$stream->streamEvent( 'Unit' );
		$stream->streamEvent( "Test\nUnit", 'testEvent' );
		$stream->endStream();
		$this->expectOutputString( $expectedOutput );
	}
}
