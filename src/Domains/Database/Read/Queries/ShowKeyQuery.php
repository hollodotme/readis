<?php declare(strict_types=1);

namespace hollodotme\Readis\Domains\Database\Read\Queries;

use Fortuneglobe\IceHawk\DomainQuery;

final class ShowKeyQuery extends DomainQuery
{
	/**
	 * @return string
	 */
	public function getServerKey()
	{
		return $this->getRequestValue( 'serverKey' );
	}

	/**
	 * @return string
	 */
	public function getDatabase()
	{
		return $this->getRequestValue( 'database' );
	}

	/**
	 * @return string
	 */
	public function getKey()
	{
		return $this->getRequestValue( 'key' );
	}

	/**
	 * @return null|string
	 */
	public function getHashKey()
	{
		return $this->getRequestValue( 'hashKey' );
	}
}
