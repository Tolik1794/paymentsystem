<?php

namespace payment\PaymentSystem\LiqPayHandler\model;

use Exception;
use payment\PaymentSystem\PaymentStatusAbstract;
use payment\PaymentSystem\PaymentStatusInterface;

class PaymentStatus extends PaymentStatusAbstract implements PaymentStatusInterface
{
	/**
	 * @throws Exception
	 */
	public function initFromResponse(): void
	{
		$this->error = $this->response['error'] ?? null;
		if ($this->error) return;

		$this->statusDescription = $this->response['status_ru'] ?? null;
		$this->status = $this->response['status'] ?? null;
		$this->date = !empty($this->response['date']) ? new \DateTime($this->response['date']): null;
		$this->amount = $this->response['amount'] ?? null;
		$this->refundAmount = $this->response['refund_amount'] ?? null;
	}
}