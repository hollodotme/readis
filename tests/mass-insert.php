<?php
/**
 *
 * @author hollodotme
 */

header( 'Content-Type: text/plain; charset=utf-8' );
ob_implicit_flush( true );

function gen_redis_proto()
{
	$args = func_get_args();

	$proto = '';
	$proto .= sprintf( "*%d\r\n", count( $args ) );

	foreach ( $args as $arg )
	{
		$proto .= sprintf( "$%d\r\n%s\r\n", mb_strlen( $arg ), $arg );
	}

	echo $proto;
}

for ( $i = 0; $i < 1000; $i++ )
{
	gen_redis_proto( "SET", "Key{$i}", "Value{$i}" );
}