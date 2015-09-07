<?php
/**
 * @author h.woltersdorf
 */

error_reporting( E_ALL );
ini_set( 'display_errors', 1 );

$socket = fsockopen( "127.0.0.1", 6379 );

if ( !$socket )
{
	return;
}
stream_set_blocking( $socket, 0 );
stream_set_blocking( STDIN, 0 );

do
{
	echo "$ ";
	$read   = array( $socket, STDIN );
	$write  = null;
	$except = null;

	if ( !is_resource( $socket ) )
	{
		return;
	}
	$num_changed_streams = @stream_select( $read, $write, $except, null );
	if ( feof( $socket ) )
	{
		return;
	}

	if ( $num_changed_streams === 0 )
	{
		continue;
	}
	if ( false === $num_changed_streams )
	{
		/* Error handling */
		var_dump( $read );
		echo "Continue\n";
		die;
	}
	elseif ( $num_changed_streams > 0 )
	{
		echo "\r";
		$data = fread( $socket, 4096 );
		if ( $data !== "" )
		{
			echo "<<< $data";
		}

		$data2 = fread( STDIN, 4096 );

		if ( $data2 !== "" )
		{
			echo ">>> $data2";
			fwrite( $socket, trim( $data2 ) );
		}
	}
}
while ( true );