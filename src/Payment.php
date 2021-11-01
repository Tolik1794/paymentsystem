<?php

namespace payment\PaymentSystem;

use payment\PaymentSystem\PaymentCancelInterface;
use payment\PaymentSystem\PaymentCompleteInterface;
use payment\PaymentSystem\PaymentReturnCashInterface;
use payment\PaymentSystem\PaymentStatusInterface;

class Payment
{
	private PaymentInterface $model;
	private ?PaymentReturnCashInterface $paymentReturnCash = null;

	/**
	 * @return PaymentInterface
	 */
	public function getModel(): PaymentInterface
	{
		return $this->model;
	}

	/**
	 * @return PaymentReturnCashInterface|null
	 */
	public function getPaymentReturnCash(): ?PaymentReturnCashInterface
	{
		if (!$this->paymentReturnCash) {
			$this->paymentReturnCash = $this->model->initReturnCashModel();
		}

		return $this->paymentReturnCash;
	}

	/**
	 * @param PaymentInitInterface $paymentInit
	 * @param string               $paymentClass
	 * @return $this
	 */
	public function init(PaymentInitInterface $paymentInit, string $paymentClass): self
	{
		$payment = sprintf('payment\\PaymentSystem\\%1$s\\%1$s', $paymentClass);

		/** @var PaymentInterface $model */
		$this->model = new $payment();
		$paymentInit->setPaymentAuth($this->model);

		return $this;
	}

	/**
	 * @param string $order_id
	 * @return PaymentStatusInterface
	 */
	public function status(string $order_id): PaymentStatusInterface
	{
		return $this->model->status($order_id);
	}

	/**
	 * @param string $order_id
	 * @return PaymentCompleteInterface
	 */
	public function complete(string $order_id): PaymentCompleteInterface
	{
		return $this->model->complete($order_id);
	}

	/**
	 * @param string $order_id
	 * @return PaymentCancelInterface
	 */
	public function cancel(string $order_id): PaymentCancelInterface
	{
		return $this->model->cancel($order_id);
	}

	/**
	 * @param string $order_id
	 * @param float  $amount
	 * @return array
	 */
	public function return(string $order_id, float $amount): array
	{
		return $this->model->return($order_id, $amount);
	}
}