<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Database\Read\Queries;

use Fortuneglobe\IceHawk\DomainQuery;

/**
 * Class SearchKeysQuery
 *
 * @package hollodotme\Readis\Domains\Database\Read\Queries
 */
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
