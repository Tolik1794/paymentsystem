<?php

namespace payment\PaymentSystem\LiqPayHandler\model;


use payment\PaymentSystem\PaymentReturnCashAbstract;
use payment\PaymentSystem\PaymentReturnCashInterface;

class PaymentReturnCash extends PaymentReturnCashAbstract implements PaymentReturnCashInterface
{
	public function initFromResponse(array $response): PaymentReturnCashInterface
	{
		$this->response = $response;
		$this->status = $response['status'] ?? null;
		$this->error = $response['err_description'] ?? null;
		$this->message = $response['message'] ?? null;

		return $this;
	}
}