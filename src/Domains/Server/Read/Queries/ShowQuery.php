<?php declare(strict_types=1);
/**
 *
 * @author hollodotme
 */

namespace hollodotme\Readis\Domains\Server\Read\Queries;

use Fortuneglobe\IceHawk\DomainQuery;

/**
 * Class ShowQuery
 *
 * @package hollodotme\Readis\Domains\Server\Read\Queries
 */
final class ShowQuery extends DomainQuery
{
	/**
	 * @return string
	 */
	public function getServerKey()
	{
		return $this->getRequestValue( 'serverKey' );
	}
}
