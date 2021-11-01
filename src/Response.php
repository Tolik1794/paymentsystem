<?php

namespace payment\PaymentSystem;

use DateTime;
use Exception;
use ReflectionClass;

class Response
{
	const CANCEL = 'cancel';
	const STATUS = 'status';
	const COMPLETE = 'complete';
	const RETURN = 'return';

	public array $data;

	/**
	 * @param array  $response
	 * @param string $type
	 * @param string $status
	 * @param string $message
	 * @throws Exception
	 */
	public function __construct(
		array $response,
		public string $type,
		public string $status = 'success',
		public string $message = ''
	)
	{
		if (!in_array($type, $this->getConstants())) {
			throw new Exception(sprintf('Unknown request type: %s', $this->type));
		}

		$this->data = $response;
	}

	/**
	 * @return array
	 */
	private function getConstants(): array
	{
		$oClass = new ReflectionClass(__CLASS__);
		return $oClass->getConstants();
	}

	/**
	 * Convert unix millisecond time to date time.
	 *
	 * @param int $unix_milli_seconds
	 * @return string
	 * @throws Exception
	 */
	private function convertUnixMilliSeconds(int $unix_milli_seconds): string
	{
		$date = new DateTime();
		$date->setTimestamp($unix_milli_seconds / 1000);

		return $date->format("Y-m-d H:i:s");
	}
}