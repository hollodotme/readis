<?php declare(strict_types=1);

namespace hollodotme\Readis\Domains\Database\Read\Queries;

use Fortuneglobe\IceHawk\DomainQuery;

final class SearchKeysQuery extends DomainQuery
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
	public function getLimit()
	{
		return $this->getRequestValue( 'limit' );
	}

	/**
	 * @return string
	 */
	public function getSearchPattern()
	{
		return $this->getRequestValue( 'searchPattern' );
	}
}
