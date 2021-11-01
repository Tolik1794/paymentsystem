<?php

namespace payment\PaymentSystem;

use payment\PaymentSystem\PaymentInterface;

abstract class PaymentReturnCashAbstract
{
	protected float $maxAmount;
	protected float $amount;
	protected array $response;
	protected ?string $error = null;
	protected ?float $returnedAmount = null;
	protected ?string $status = '';
	protected ?string $message = null;
	protected ?string $errorPrefix = null;
	protected ?string $errorPostfix = null;


	/**
	 * @return string|null
	 */
	public function getError(): ?string
	{
		return $this->error;
	}

	/**
	 * @return float|null
	 */
	public function getReturnedAmount(): ?float
	{
		return $this->returnedAmount;
	}

	/**
	 * @return array
	 */
	public function getResponse(): array
	{
		return $this->response;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return get_object_vars($this);
	}

	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function setErrorPrefix(string $prefix): PaymentReturnCashInterface
	{
		$this->errorPrefix = $prefix;

		return $this;
	}

	/**
	 * @param string $postfix
	 * @return $this
	 */
	public function setErrorPostfix(string $postfix): PaymentReturnCashInterface
	{
		$this->errorPostfix = $postfix;

		return $this;
	}
	/**
	 * @return float
	 */
	public function getMaxAmount(): float
	{
		return $this->maxAmount;
	}

	/**
	 * @param float $maxAmount
	 */
	public function setMaxAmount(float $maxAmount): void
	{
		$this->maxAmount = $maxAmount;
	}

	/**
	 * @return float
	 */
	public function getAmount(): float
	{
		return $this->amount;
	}

	/**
	 * @param float $amount
	 */
	public function setAmount(float $amount): void
	{
		$this->amount = $amount;
	}

	/**
	 * @return string|null
	 */
	public function getMessage(): ?string
	{
		return $this->message;
	}
}