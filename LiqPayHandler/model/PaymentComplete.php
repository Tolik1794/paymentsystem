<?php

namespace payment\PaymentSystem\LiqPayHandler\model;


use payment\PaymentSystem\PaymentCompleteAbstract;
use payment\PaymentSystem\PaymentCompleteInterface;

class PaymentComplete extends PaymentCompleteAbstract implements PaymentCompleteInterface
{
	public function initFromResponse(): void
	{
		$this->status = $this->response['status'] ?? null;
		$this->acceptedAmount = $this->response['amount'] ?? null;
		$this->error = $this->response['err_description'] ?? null;
	}
}