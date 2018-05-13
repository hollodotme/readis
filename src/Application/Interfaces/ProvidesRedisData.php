<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\Interfaces;

use hollodotme\Readis\Infrastructure\Redis\Exceptions\ConnectionFailedException;

interface ProvidesRedisData
{
	/**
	 * @param int $database
	 *
	 * @throws ConnectionFailedException
	 */
	public function selectDatabase( int $database ) : void;

	/**
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getServerConfig() : array;

	/**
	 * @return int
	 * @throws ConnectionFailedException
	 */
	public function getSlowLogCount() : int;

	/**
	 * @param int $limit
	 *
	 * @return array|ProvidesSlowLogData[]
	 * @throws \Exception
	 * @throws ConnectionFailedException
	 */
	public function getSlowLogEntries( int $limit = 100 ) : array;

	/**
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getServerInfo() : array;

	/**
	 * @param string $keyPattern
	 *
	 * @return array
	 * @throws ConnectionFailedException
	 */
	public function getKeys( string $keyPattern = '*' ) : array;

	/**
	 * @param string   $keyPattern
	 * @param int|null $limit
	 *
	 * @return array|ProvidesKeyInfo[]
	 * @throws ConnectionFailedException
	 */
	public function getKeyInfoObjects( string $keyPattern, ?int $limit ) : array;

	/**
	 * @param string $key
	 *
	 * @return ProvidesKeyInfo
	 * @throws ConnectionFailedException
	 */
	public function getKeyInfoObject( string $key ) : ProvidesKeyInfo;

	/**
	 * @param string $key
	 *
	 * @return string
	 * @throws ConnectionFailedException
	 */
	public function getValue( string $key ) : string;

	/**
	 * @param string $key
	 * @param string $hashKey
	 *
	 * @return string
	 * @throws ConnectionFailedException
	 */
	public function getHashValue( string $key, string $hashKey ) : string;

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllHashValues( string $key ) : array;

	/**
	 * @param string $key
	 * @param int    $index
	 *
	 * @throws ConnectionFailedException
	 * @return string
	 */
	public function getListElement( string $key, int $index ) : string;

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllListElements( string $key ) : array;

	/**
	 * @param string $key
	 * @param int    $index
	 *
	 * @throws ConnectionFailedException
	 * @return string
	 */
	public function getSetMember( string $key, int $index ) : string;

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllSetMembers( string $key ) : array;

	/**
	 * @param string $key
	 *
	 * @throws ConnectionFailedException
	 * @return array
	 */
	public function getAllSortedSetMembers( string $key ) : array;

	/**
	 * @param string $command
	 *
	 * @throws ConnectionFailedException
	 * @return bool
	 */
	public function commandExists( string $command ) : bool;
}