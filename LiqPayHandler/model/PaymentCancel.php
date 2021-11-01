<?php

namespace payment\PaymentSystem\LiqPayHandler\model;


use payment\PaymentSystem\PaymentCancelAbstract;
use payment\PaymentSystem\PaymentCancelInterface;

class PaymentCancel extends PaymentCancelAbstract implements PaymentCancelInterface
{
	public function initFromResponse(): void
	{
		$this->status = $this->response['status'] ?? null;
		$this->canceledAmount = $this->response['amount'] ?? null;
		$this->error = $this->response['err_description'] ?? null;
	}
}