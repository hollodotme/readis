<?php declare(strict_types=1);

namespace hollodotme\Readis\Domains\Server\Read\Queries;

use Fortuneglobe\IceHawk\DomainQuery;

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
