<?php declare(strict_types=1);

namespace hollodotme\Readis\Application\ReadModel\Results;

use hollodotme\Readis\Application\ReadModel\Constants\ResultType;

abstract class AbstractResult
{
	/** @var int */
	private $type;

	/** @var string */
	private $message;

	public function __construct( int $type = ResultType::SUCCESS, string $message = '' )
	{
		$this->type    = $type;
		$this->message = $message;
	}

	final public function succeeded() : bool
	{
		return (ResultType::SUCCESS === $this->type);
	}

	final public function failed() : bool
	{
		return (ResultType::FAILURE === $this->type);
	}

	final public function getMessage() : string
	{
		return $this->message;
	}
}
