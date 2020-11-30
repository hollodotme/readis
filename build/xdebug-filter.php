<?php declare(strict_types=1);

if ( function_exists( 'xdebug_set_filter' ) )
{
	/** @noinspection PhpComposerExtensionStubsInspection */
	/** @noinspection PhpUndefinedConstantInspection */
	xdebug_set_filter(
		XDEBUG_FILTER_CODE_COVERAGE,
		defined( 'XDEBUG_PATH_WHITELIST' ) ? XDEBUG_PATH_WHITELIST : XDEBUG_PATH_INCLUDE,
		[dirname( __DIR__ ) . '/src/']
	);
}
